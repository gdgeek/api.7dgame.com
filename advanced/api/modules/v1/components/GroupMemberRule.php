<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Group;
use api\modules\v1\models\GroupUser;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

/**
 * GroupMemberRule - 检查当前用户是否是小组成员
 * 
 * 使用场景：
 * - 只有小组成员才能访问/操作小组资源
 * 
 * 优化：
 * - 使用缓存减少数据库查询
 * - 同一请求内使用静态变量避免重复查询
 */
class GroupMemberRule extends Rule
{
    public $name = 'group_member_rule';
    
    /**
     * 缓存时间（秒）
     */
    const CACHE_DURATION = 300; // 5分钟
    
    /**
     * 请求内缓存（避免同一请求多次查询）
     * @var array
     */
    private static $memberCache = [];

    /**
     * 请求内缓存 group.owner_id（避免同一请求重复查 group）
     * @var array<int, int>
     */
    private static $groupOwnerCache = [];

    /**
     * group.owner_id 的应用缓存 key
     */
    private const GROUP_OWNER_ID_CACHE_KEY_PREFIX = 'group_owner_id_';

    /**
     * 从请求参数中获取 group_id（不查询数据库）
     * @param array $params
     * @return int
     * @throws BadRequestHttpException
     */
    private function getGroupId($params)
    {
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        
        $groupId = $params['group_id'] ?? $post['group_id'] ?? $get['group_id'] 
            ?? $params['id'] ?? $post['id'] ?? $get['id']
            ?? null;
       
        if (!$groupId) {
            throw new BadRequestHttpException("group_id is required");
        }

        return (int)$groupId;
    }

    /**
     * 获取小组创建者 user_id（保证小组存在）
     * @param int $groupId
     * @return int
     * @throws BadRequestHttpException
     */
    private function getGroupOwnerId($groupId)
    {
        $groupId = (int)$groupId;
        if (isset(self::$groupOwnerCache[$groupId])) {
            return self::$groupOwnerCache[$groupId];
        }

        $cacheKey = self::GROUP_OWNER_ID_CACHE_KEY_PREFIX . $groupId;
        $cachedOwnerId = Yii::$app->cache->get($cacheKey);
        if ($cachedOwnerId !== false && $cachedOwnerId !== null) {
            self::$groupOwnerCache[$groupId] = (int)$cachedOwnerId;
            return self::$groupOwnerCache[$groupId];
        }

        $ownerId = Group::find()
            ->select('user_id')
            ->where(['id' => $groupId])
            ->scalar();

        if ($ownerId === false || $ownerId === null) {
            throw new BadRequestHttpException("Group not found");
        }

        self::$groupOwnerCache[$groupId] = (int)$ownerId;
        Yii::$app->cache->set($cacheKey, self::$groupOwnerCache[$groupId], self::CACHE_DURATION);
        return self::$groupOwnerCache[$groupId];
    }

    /**
     * 清除 group.owner_id 缓存
     * 在小组转让/删除时调用
     * @param int $groupId
     */
    public static function clearGroupOwnerCache($groupId)
    {
        if (!$groupId) {
            return;
        }

        $groupId = (int)$groupId;
        $cacheKey = self::GROUP_OWNER_ID_CACHE_KEY_PREFIX . $groupId;
        Yii::$app->cache->delete($cacheKey);
        unset(self::$groupOwnerCache[$groupId]);
    }

    /**
     * 检查用户是否是小组成员（带缓存）
     * @param int $userId
     * @param int $groupId
     * @return bool
     */
    private function isMember($userId, $groupId)
    {
        $userId = (int)$userId;
        $groupId = (int)$groupId;
        $cacheKey = "group_member_{$userId}_{$groupId}";
        
        // 1. 先检查请求内缓存（最快）
        if (isset(self::$memberCache[$cacheKey])) {
            return self::$memberCache[$cacheKey];
        }
        
        // 2. 再检查应用缓存
        $cache = Yii::$app->cache;

        // 注意：Yii cache get() 以 false 表示未命中，无法区分“命中且值为 false”。
        // 这里缓存存 0/1 整数，从而可用 !== false 判断。
        $cached = $cache->get($cacheKey);
        if ($cached !== false) {
            $result = (bool)(int)$cached;
            self::$memberCache[$cacheKey] = $result;
            return $result;
        }
        
        // 3. 查询数据库
        $result = GroupUser::find()
            ->where(['user_id' => $userId, 'group_id' => $groupId])
            ->exists();
        
        // 4. 写入缓存
        $cache->set($cacheKey, (int)$result, self::CACHE_DURATION);
        self::$memberCache[$cacheKey] = $result;
        
        return $result;
    }
    
    /**
     * 清除用户的小组成员缓存
     * 在用户加入/退出小组时调用
     * 
     * @param int $userId
     * @param int $groupId
     */
    public static function clearCache($userId, $groupId)
    {
        if (!$userId || !$groupId) {
            return;
        }

        $userId = (int)$userId;
        $groupId = (int)$groupId;
        $cacheKey = "group_member_{$userId}_{$groupId}";
        Yii::$app->cache->delete($cacheKey);
        unset(self::$memberCache[$cacheKey]);
    }

    /**
     * 执行规则检查
     * @param string|int $user 用户 ID
     * @param \yii\rbac\Item $item 权限项
     * @param array $params 参数
     * @return bool
     */
    public function execute($user, $item, $params)
    {
        if (!$user) {
            return false;
        }

        $userId = (int)$user;
        $groupId = $this->getGroupId($params);

        // 先确保 group 存在，并优先判断是否为创建者（可避免一次 GroupUser exists）
        $ownerId = $this->getGroupOwnerId($groupId);
        if ($ownerId === $userId) {
            return true;
        }

        // 再判断是否为成员（走缓存）
        return $this->isMember($userId, $groupId);
    }
}

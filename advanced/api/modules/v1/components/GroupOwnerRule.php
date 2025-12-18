<?php

namespace api\modules\v1\components;

use api\modules\v1\models\Group;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

/**
 * GroupOwnerRule - 检查当前用户是否是小组创建者
 * 
 * 使用场景：
 * - 只有小组创建者才能进行某些操作（如删除小组、修改小组设置等）
 * 
 * 优化：
 * - 使用缓存减少数据库查询
 * - 同一请求内使用静态变量避免重复查询
 */
class GroupOwnerRule extends Rule
{
    public $name = 'group_owner_rule';
    
    /**
     * 缓存时间（秒）
     */
    const CACHE_DURATION = 300; // 5分钟

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
     * 检查用户是否是小组创建者
     * @param int $userId
     * @param int $groupId
     * @return bool
     */
    private function isOwner($userId, $groupId)
    {
        return $this->getGroupOwnerId($groupId) === (int)$userId;
    }

    /**
     * 清除用户的小组创建者缓存
     * 在小组转让/删除时调用
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

        // 兼容：删除旧的 per-user+group key（如果历史遗留还在）
        Yii::$app->cache->delete("group_owner_{$userId}_{$groupId}");

        // 新缓存：按 groupId 缓存 ownerId
        $cacheKey = self::GROUP_OWNER_ID_CACHE_KEY_PREFIX . $groupId;
        Yii::$app->cache->delete($cacheKey);
        unset(self::$groupOwnerCache[$groupId]);
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

        $groupId = $this->getGroupId($params);

        // 先走缓存/一次查询判断归属，避免额外 findOne
        return $this->isOwner($user, $groupId);
    }
}

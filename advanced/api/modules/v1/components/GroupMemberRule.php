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
     * 从请求参数中获取 Group
     * @param array $params
     * @return Group
     * @throws BadRequestHttpException
     */
    private function getGroup($params)
    {
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        
        $groupId = $params['group_id'] ?? $post['group_id'] ?? $get['group_id'] 
            ?? $params['id'] ?? $post['id'] ?? $get['id']
            ?? null;
       
        if (!$groupId) {
            throw new BadRequestHttpException("group_id is required");
        }
  
        $group = Group::findOne($groupId);
        if (!$group) {
            throw new BadRequestHttpException("Group not found");
        }
        
        return $group;
    }

    /**
     * 检查用户是否是小组成员（带缓存）
     * @param int $userId
     * @param int $groupId
     * @return bool
     */
    private function isMember($userId, $groupId)
    {
        $cacheKey = "group_member_{$userId}_{$groupId}";
        
        // 1. 先检查请求内缓存（最快）
        if (isset(self::$memberCache[$cacheKey])) {
            return self::$memberCache[$cacheKey];
        }
        
        // 2. 再检查应用缓存
        $cache = Yii::$app->cache;
        $result = $cache->get($cacheKey);
        
        if ($result !== false) {
            self::$memberCache[$cacheKey] = $result;
            return $result;
        }
        
        // 3. 查询数据库
        $result = GroupUser::find()
            ->where(['user_id' => $userId, 'group_id' => $groupId])
            ->exists();
        
        // 4. 写入缓存
        $cache->set($cacheKey, $result, self::CACHE_DURATION);
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

        $group = $this->getGroup($params);

        // 检查当前用户是否是小组成员
        if ($this->isMember($user, $group->id)) {
            return true;
        }

        // 检查当前用户是否是小组创建者（不需要缓存，Group 已经查出来了）
        if ($group->user_id == $user) {
            return true;
        }

        return false;
    }
}

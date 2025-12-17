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
     * 请求内缓存（避免同一请求多次查询）
     * @var array
     */
    private static $ownerCache = [];

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
     * 检查用户是否是小组创建者（带缓存）
     * @param int $userId
     * @param int $groupId
     * @return bool
     */
    private function isOwner($userId, $groupId)
    {
        $cacheKey = "group_owner_{$userId}_{$groupId}";
        
        // 1. 先检查请求内缓存（最快）
        if (isset(self::$ownerCache[$cacheKey])) {
            return self::$ownerCache[$cacheKey];
        }
        
        // 2. 再检查应用缓存
        $cache = Yii::$app->cache;
        $result = $cache->get($cacheKey);
        
        if ($result !== false) {
            self::$ownerCache[$cacheKey] = $result;
            return $result;
        }
        
        // 3. 查询数据库
        $result = Group::find()
            ->where(['id' => $groupId, 'user_id' => $userId])
            ->exists();
        
        // 4. 写入缓存
        $cache->set($cacheKey, $result, self::CACHE_DURATION);
        self::$ownerCache[$cacheKey] = $result;
        
        return $result;
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
        $cacheKey = "group_owner_{$userId}_{$groupId}";
        Yii::$app->cache->delete($cacheKey);
        unset(self::$ownerCache[$cacheKey]);
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

        // 检查当前用户是否是小组创建者
        return $this->isOwner($user, $group->id);
    }
}

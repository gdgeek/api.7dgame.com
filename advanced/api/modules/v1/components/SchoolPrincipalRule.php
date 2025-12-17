<?php

namespace api\modules\v1\components;

use api\modules\v1\models\EduSchool;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

/**
 * SchoolPrincipalRule - 检查当前用户是否是学校的校长
 * 
 * 使用场景：
 * - 只有校长才能进行某些操作（如管理学校、管理班级等）
 * 
 * 优化：
 * - 使用缓存减少数据库查询
 * - 同一请求内使用静态变量避免重复查询
 */
class SchoolPrincipalRule extends Rule
{
    public $name = 'school_principal_rule';
    
    /**
     * 缓存时间（秒）
     */
    const CACHE_DURATION = 300; // 5分钟
    
    /**
     * 请求内缓存（避免同一请求多次查询）
     * @var array
     */
    private static $principalCache = [];

    /**
     * 从请求参数中获取 School
     * @param array $params
     * @return EduSchool
     * @throws BadRequestHttpException
     */
    private function getSchool($params)
    {
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        
        $schoolId = $params['school_id'] ?? $post['school_id'] ?? $get['school_id'] 
            ?? $params['id'] ?? $post['id'] ?? $get['id']
            ?? null;
       
        if (!$schoolId) {
            throw new BadRequestHttpException("school_id is required");
        }
  
        $school = EduSchool::findOne($schoolId);
        if (!$school) {
            throw new BadRequestHttpException("School not found");
        }
        
        return $school;
    }

    /**
     * 检查用户是否是学校校长（带缓存）
     * @param int $userId
     * @param int $schoolId
     * @return bool
     */
    private function isPrincipal($userId, $schoolId)
    {
        $cacheKey = "school_principal_{$userId}_{$schoolId}";
        
        // 1. 先检查请求内缓存（最快）
        if (isset(self::$principalCache[$cacheKey])) {
            return self::$principalCache[$cacheKey];
        }
        
        // 2. 再检查应用缓存
        $cache = Yii::$app->cache;
        $result = $cache->get($cacheKey);
        
        if ($result !== false) {
            self::$principalCache[$cacheKey] = $result;
            return $result;
        }
        
        // 3. 查询数据库
        $result = EduSchool::find()
            ->where(['id' => $schoolId, 'principal_id' => $userId])
            ->exists();
        
        // 4. 写入缓存
        $cache->set($cacheKey, $result, self::CACHE_DURATION);
        self::$principalCache[$cacheKey] = $result;
        
        return $result;
    }
    
    /**
     * 清除用户的学校校长缓存
     * 在学校更换校长时调用
     * 
     * @param int $userId
     * @param int $schoolId
     */
    public static function clearCache($userId, $schoolId)
    {
        $cacheKey = "school_principal_{$userId}_{$schoolId}";
        Yii::$app->cache->delete($cacheKey);
        unset(self::$principalCache[$cacheKey]);
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

        $school = $this->getSchool($params);

        // 检查当前用户是否是学校校长
        return $this->isPrincipal($user, $school->id);
    }
}

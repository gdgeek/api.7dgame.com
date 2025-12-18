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
     * 请求内缓存 school.principal_id（避免同一请求重复查 edu_school）
     * @var array<int, int|null>
     */
    private static $schoolPrincipalIdCache = [];

    /**
     * school.principal_id 的应用缓存 key
     */
    private const SCHOOL_PRINCIPAL_ID_CACHE_KEY_PREFIX = 'school_principal_id_';

    /**
     * 从请求参数中获取 school_id（不查询数据库）
     * @param array $params
     * @return int
     * @throws BadRequestHttpException
     */
    private function getSchoolId($params)
    {
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();

        $schoolId = $params['school_id'] ?? $post['school_id'] ?? $get['school_id']
            ?? $params['id'] ?? $post['id'] ?? $get['id']
            ?? null;

        if (!$schoolId) {
            throw new BadRequestHttpException("school_id is required");
        }

        return (int)$schoolId;
    }

    /**
     * 获取学校 principal_id（保证学校存在，带缓存）
     * @param int $schoolId
     * @return int|null
     * @throws BadRequestHttpException
     */
    private function getSchoolPrincipalId($schoolId)
    {
        $schoolId = (int)$schoolId;
        if (array_key_exists($schoolId, self::$schoolPrincipalIdCache)) {
            return self::$schoolPrincipalIdCache[$schoolId];
        }

        $cacheKey = self::SCHOOL_PRINCIPAL_ID_CACHE_KEY_PREFIX . $schoolId;
        $cached = Yii::$app->cache->get($cacheKey);
        if ($cached !== false && $cached !== null) {
            $principalId = ((int)$cached === -1) ? null : (int)$cached;
            self::$schoolPrincipalIdCache[$schoolId] = $principalId;
            return $principalId;
        }

        $principalId = EduSchool::find()
            ->select('principal_id')
            ->where(['id' => $schoolId])
            ->scalar();

        if ($principalId === false || $principalId === null) {
            // principal_id 为 null 与 school 不存在无法区分，因此额外确认学校是否存在
            $exists = EduSchool::find()->where(['id' => $schoolId])->exists();
            if (!$exists) {
                throw new BadRequestHttpException("School not found");
            }

            self::$schoolPrincipalIdCache[$schoolId] = null;
            Yii::$app->cache->set($cacheKey, -1, self::CACHE_DURATION);
            return null;
        }

        self::$schoolPrincipalIdCache[$schoolId] = (int)$principalId;
        Yii::$app->cache->set($cacheKey, self::$schoolPrincipalIdCache[$schoolId], self::CACHE_DURATION);
        return self::$schoolPrincipalIdCache[$schoolId];
    }
    
    /**
     * 清除用户的学校校长缓存
     * 在学校更换校长时调用
     * 
     * @param int $userId
     */
    public static function clearCache($userId)
    {
        if (!$userId) {
            return;
        }

        $userId = (int)$userId;
        // 兼容历史：之前按 userId 缓存 principal_schools_{userId}
        Yii::$app->cache->delete("principal_schools_{$userId}");
    }

    /**
     * 清除学校 principal_id 缓存
     * 在学校更换校长/删除时调用
     *
     * @param int $schoolId
     */
    public static function clearSchoolCache($schoolId)
    {
        if (!$schoolId) {
            return;
        }

        $schoolId = (int)$schoolId;
        $cacheKey = self::SCHOOL_PRINCIPAL_ID_CACHE_KEY_PREFIX . $schoolId;
        Yii::$app->cache->delete($cacheKey);
        unset(self::$schoolPrincipalIdCache[$schoolId]);
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
        $schoolId = $this->getSchoolId($params);
        $principalId = $this->getSchoolPrincipalId($schoolId);
        return $principalId !== null && $principalId === $userId;
    }
}

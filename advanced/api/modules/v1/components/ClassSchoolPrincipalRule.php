<?php

namespace api\modules\v1\components;

use api\modules\v1\models\EduClass;
use api\modules\v1\models\EduSchool;
use Yii;
use yii\rbac\Rule;
use yii\web\BadRequestHttpException;

/**
 * ClassSchoolPrincipalRule - 检查当前用户是否为“班级所属学校”的校长
 *
 * 判定逻辑：edu_class.school_id -> edu_school.principal_id == 当前用户
 *
 * 优化：
 * - 规则校验使用一次 JOIN + EXISTS 查询
 * - 同一请求内使用静态缓存避免重复查询
 */
class ClassSchoolPrincipalRule extends Rule
{
    public $name = 'class_school_principal_rule';

    /**
     * 缓存时间（秒）
     */
    const CACHE_DURATION = 300; // 5分钟

    /**
        * 请求内缓存 (userId,classId) => bool
        * @var array<string, bool>
     */
        private static $principalOfClassCache = [];

    /**
     * 从请求参数中获取 class_id（不查询数据库）
     * @param array $params
     * @return int
     * @throws BadRequestHttpException
     */
    private function getClassId($params)
    {
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();

        $classId = $params['class_id'] ?? $post['class_id'] ?? $get['class_id']
            ?? $params['id'] ?? $post['id'] ?? $get['id']
            ?? null;

        if (!$classId) {
            throw new BadRequestHttpException('class_id is required');
        }

        return (int)$classId;
    }

    /**
     * 清除班级所属学校缓存
     * 在班级更新/删除（或变更 school_id）时调用
     *
     * @param int $classId
     */
    public static function clearCache($classId)
    {
        if (!$classId) {
            return;
        }

        $classId = (int)$classId;

        // 清除同请求缓存（按 classId 过滤）
        foreach (array_keys(self::$principalOfClassCache) as $key) {
            if (str_ends_with($key, ":c{$classId}")) {
                unset(self::$principalOfClassCache[$key]);
            }
        }

        // 兼容历史：如果曾缓存过 class_school_id_{classId}，顺便删掉
        Yii::$app->cache->delete('class_school_id_' . $classId);
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
        $classId = $this->getClassId($params);

        $cacheKey = "u{$userId}:c{$classId}";
        if (isset(self::$principalOfClassCache[$cacheKey])) {
            return self::$principalOfClassCache[$cacheKey];
        }

        // 方案B：一次 JOIN + EXISTS 查询（班级不存在与非校长都会返回 false）
        $result = (new \yii\db\Query())
            ->from(['c' => EduClass::tableName()])
            ->innerJoin(['s' => EduSchool::tableName()], 's.id = c.school_id')
            ->where(['c.id' => $classId, 's.principal_id' => $userId])
            ->exists();

        self::$principalOfClassCache[$cacheKey] = (bool)$result;
        return self::$principalOfClassCache[$cacheKey];
    }
}

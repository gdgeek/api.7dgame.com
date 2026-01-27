<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use api\modules\v1\models\Meta;
use api\modules\v1\models\Version;
use OpenApi\Annotations as OA;
use yii\base\Exception;


/**
 * @OA\Tag(
 *     name="System",
 *     description="系统升级接口"
 * )
 */
class SystemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/system/test",
     *     tags={"System"},
     *     summary="系统环境检查",
     *     description="检查数据库表、PHP 配置和依赖是否正常",
     *     @OA\Response(
     *         response=200,
     *         description="检查结果",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="checks", type="object")
     *         )
     *     )
     * )
     */
    public function actionTest()
    {
        $checks = [];
        $allPassed = true;

        // 1. 检查数据库连接
        try {
            Yii::$app->db->open();
            $checks['database_connection'] = [
                'status' => 'ok',
                'message' => '数据库连接正常',
                'driver' => Yii::$app->db->driverName,
            ];
        } catch (\Exception $e) {
            $checks['database_connection'] = [
                'status' => 'error',
                'message' => '数据库连接失败: ' . $e->getMessage(),
            ];
            $allPassed = false;
        }

        // 2. 检查必需的数据库表
        $requiredTables = [
            'version',
            'meta',
            'verse',
            'meta_code',
            'verse_code',
            'tags',
            'property',
            'verse_property',
            'verse_tags',
        ];

        $checks['database_tables'] = [];
        foreach ($requiredTables as $table) {
            try {
                $exists = Yii::$app->db->schema->getTableSchema($table) !== null;
                $checks['database_tables'][$table] = [
                    'status' => $exists ? 'ok' : 'missing',
                    'exists' => $exists,
                ];
                if (!$exists) {
                    $allPassed = false;
                }
            } catch (\Exception $e) {
                $checks['database_tables'][$table] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
                $allPassed = false;
            }
        }

        // 3. 检查 PHP 配置
        $checks['php_config'] = [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'can_set_time_limit' => function_exists('set_time_limit'),
            'can_set_memory_limit' => function_exists('ini_set'),
        ];

        // 4. 检查 UUID 依赖
        $checks['dependencies'] = [
            'uuid_helper' => [
                'class_exists' => class_exists('\common\components\UuidHelper'),
                'status' => class_exists('\common\components\UuidHelper') ? 'ok' : 'missing',
            ],
            'yii_string_helper' => [
                'class_exists' => class_exists('\yii\helpers\StringHelper'),
                'status' => class_exists('\yii\helpers\StringHelper') ? 'ok' : 'missing',
            ],
        ];

        if (!class_exists('\common\components\UuidHelper')) {
            $checks['dependencies']['uuid_helper']['message'] = 'UuidHelper 未找到';
            $checks['dependencies']['uuid_helper']['suggestion'] = '请确保 common\components\UuidHelper 类存在';
        }

        // 5. 检查模型类
        $requiredModels = [
            'api\modules\v1\models\Version',
            'api\modules\v1\models\Meta',
            'api\modules\v1\models\Verse',
            'api\modules\v1\models\Tags',
            'api\modules\v1\models\Property',
            'api\modules\v1\models\VerseProperty',
        ];

        $checks['models'] = [];
        foreach ($requiredModels as $model) {
            $exists = class_exists($model);
            $checks['models'][$model] = [
                'status' => $exists ? 'ok' : 'missing',
                'exists' => $exists,
            ];
            if (!$exists) {
                $allPassed = false;
            }
        }

        // 6. 检查数据库权限（尝试简单的查询）
        try {
            Yii::$app->db->createCommand('SELECT 1')->queryOne();
            $checks['database_permissions'] = [
                'status' => 'ok',
                'message' => '数据库查询权限正常',
            ];
        } catch (\Exception $e) {
            $checks['database_permissions'] = [
                'status' => 'error',
                'message' => '数据库权限问题: ' . $e->getMessage(),
            ];
            $allPassed = false;
        }

        // 7. 检查日志目录权限
        $logPath = Yii::getAlias('@api/runtime/logs');
        $checks['log_directory'] = [
            'path' => $logPath,
            'exists' => file_exists($logPath),
            'writable' => is_writable($logPath),
            'status' => (file_exists($logPath) && is_writable($logPath)) ? 'ok' : 'error',
        ];

        // 8. 尝试获取一些统计数据
        $checks['data_statistics'] = [];
        try {
            if (isset($checks['database_tables']['meta']) && $checks['database_tables']['meta']['exists']) {
                $checks['data_statistics']['meta_count'] = Meta::find()->count();
            }
            if (isset($checks['database_tables']['verse']) && $checks['database_tables']['verse']['exists']) {
                $checks['data_statistics']['verse_count'] = Verse::find()->count();
            }
            if (isset($checks['database_tables']['version']) && $checks['database_tables']['version']['exists']) {
                $checks['data_statistics']['version_count'] = Version::find()->count();
            }
        } catch (\Exception $e) {
            $checks['data_statistics']['error'] = $e->getMessage();
        }

        return [
            'status' => $allPassed ? 'ok' : 'error',
            'message' => $allPassed ? '所有检查通过' : '部分检查失败，请查看详情',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => YII_ENV,
            'debug' => YII_DEBUG,
            'checks' => $checks,
        ];
    }

    /**
     * @OA\Post(
     *     path="/v1/system/upgrade",
     *     tags={"System"},
     *     summary="系统升级工具",
     *     description="将 Code 表数据迁移到 MetaCode 和 VerseCode",
     *     @OA\Response(
     *         response=200,
     *         description="升级结果",
     *         @OA\JsonContent(
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="success", type="integer"),
     *                 @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *             ),
     *             @OA\Property(property="verse", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="success", type="integer"),
     *                 @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     */
    public function actionUpgrade()
    {
        try {
            // 防止执行超时
            set_time_limit(0);
            ignore_user_abort(true);

            // 增加内存限制，防止大量数据处理导致内存溢出
            ini_set('memory_limit', '512M');

            $result = [
                'meta' => ['total' => 0, 'success' => 0, 'fail' => 0, 'errors' => []],
                'verse' => ['total' => 0, 'success' => 0, 'fail' => 0, 'errors' => []],
                'version' => ['status' => 'pending'],
                'tags' => ['public' => ['status' => 'pending'], 'checkin' => ['status' => 'pending']],
            ];

            // 1. Version 升级
            try {
                Version::upgrade();
                $result['version']['status'] = 'success';
            } catch (\Exception $e) {
                $result['version']['status'] = 'error';
                $result['version']['error'] = $e->getMessage();
                $result['version']['trace'] = $e->getTraceAsString();
                Yii::error("Version Upgrade Error: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'upgrade');
                throw new Exception("Version upgrade failed: " . $e->getMessage(), 500);
            }

            // 2. Tag 迁移
            try {
                $result['tags']['public'] = $this->migrateTagToProperty('public');
            } catch (\Exception $e) {
                $result['tags']['public']['status'] = 'error';
                $result['tags']['public']['error'] = $e->getMessage();
                $result['tags']['public']['trace'] = $e->getTraceAsString();
                Yii::error("Tag 'public' Migration Error: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'upgrade');
                throw new Exception("Tag 'public' migration failed: " . $e->getMessage(), 500);
            }

            try {
                $result['tags']['checkin'] = $this->migrateTagToProperty('checkin');
            } catch (\Exception $e) {
                $result['tags']['checkin']['status'] = 'error';
                $result['tags']['checkin']['error'] = $e->getMessage();
                $result['tags']['checkin']['trace'] = $e->getTraceAsString();
                Yii::error("Tag 'checkin' Migration Error: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'upgrade');
                throw new Exception("Tag 'checkin' migration failed: " . $e->getMessage(), 500);
            }

            // 3. 批量处理 Meta，使用 batch/each 减少内存占用
            // 每次处理 100 条
            foreach (Meta::find()->each(100) as $meta) {
                $result['meta']['total']++;
                try {
                    $this->upgradeMeta($meta);
                    $result['meta']['success']++;
                } catch (\Exception $e) {
                    $result['meta']['fail']++;
                    // 记录详细错误信息
                    $errorDetail = [
                        'id' => $meta->id,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ];
                    $result['meta']['errors'][] = $errorDetail;
                    Yii::error("Meta Upgrade Error ID {$meta->id}: " . json_encode($errorDetail), 'upgrade');
                }
            }

            // 4. 批量处理 Verse
            foreach (Verse::find()->each(100) as $verse) {
                $result['verse']['total']++;
                try {
                    $this->upgradeVerse($verse);
                    $result['verse']['success']++;
                } catch (\Exception $e) {
                    $result['verse']['fail']++;
                    // 记录详细错误信息
                    $errorDetail = [
                        'id' => $verse->id,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ];
                    $result['verse']['errors'][] = $errorDetail;
                    Yii::error("Verse Upgrade Error ID {$verse->id}: " . json_encode($errorDetail), 'upgrade');
                }
            }

            return $result;

        } catch (\Exception $e) {
            // 捕获所有未处理的异常
            Yii::error("Upgrade Fatal Error: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'upgrade');
            throw new Exception(
                "Upgrade process failed: " . $e->getMessage() . 
                " (File: " . $e->getFile() . ", Line: " . $e->getLine() . ")",
                500
            );
        }
    }

    /**
     * 升级 Meta 数据
     * code里面的内容要放在metacode 里面的 lua 和js
     * @param Meta $meta
     * @throws Exception
     */
    private function upgradeMeta($meta)
    {
        try {
            // 1. 确保 UUID 存在
            if (empty($meta->uuid)) {
                $meta->uuid = \common\components\UuidHelper::uuid();
                if (!$meta->save()) {
                    throw new Exception("Failed to save Meta UUID: " . json_encode($meta->errors));
                }
            }

            // 2. 刷新资源关联
            try {
                $meta->refreshResources();
            } catch (\Exception $e) {
                throw new Exception("Failed to refresh Meta resources: " . $e->getMessage(), 0, $e);
            }

            // 3. 迁移 Code 数据到 MetaCode (Lua/JS)
            $metaCode = $meta->metaCode;
            if ($metaCode && $metaCode->code) {
                $code = $metaCode->code;
                $changed = false;

                if (!empty($code->lua) && $metaCode->lua !== $code->lua) {
                    $metaCode->lua = $code->lua;
                    $changed = true;
                }
                if (!empty($code->js) && $metaCode->js !== $code->js) {
                    $metaCode->js = $code->js;
                    $changed = true;
                }

                if ($changed && !$metaCode->save()) {
                    throw new Exception("MetaCode Save Failed: " . json_encode($metaCode->errors));
                }
            }
        } catch (\Exception $e) {
            throw new Exception(
                "Meta upgrade failed (ID: {$meta->id}): " . $e->getMessage() . 
                " [File: " . $e->getFile() . ", Line: " . $e->getLine() . "]",
                0,
                $e
            );
        }
    }

    /**
     * 升级 Verse 数据
     * code里面的内容要放在versecode 里面的 lua 和js
     * @param Verse $verse
     * @throws Exception
     */
    private function upgradeVerse($verse)
    {
        try {
            // 1. 确保 UUID 存在
            if (empty($verse->uuid)) {
                $verse->uuid = \common\components\UuidHelper::uuid();
                if (!$verse->save()) {
                    throw new Exception("Failed to save Verse UUID: " . json_encode($verse->errors));
                }
            }

            // 2. 刷新 Meta 关联
            try {
                $verse->refreshMetas();
            } catch (\Exception $e) {
                throw new Exception("Failed to refresh Verse metas: " . $e->getMessage(), 0, $e);
            }

            // 3. 迁移 Code 数据到 VerseCode (Lua/JS)
            $verseCode = $verse->verseCode;
            if ($verseCode && $verseCode->code) {
                $code = $verseCode->code;
                $changed = false;

                if (!empty($code->lua) && $verseCode->lua !== $code->lua) {
                    $verseCode->lua = $code->lua;
                    $changed = true;
                }
                if (!empty($code->js) && $verseCode->js !== $code->js) {
                    $verseCode->js = $code->js;
                    $changed = true;
                }

                if ($changed && !$verseCode->save()) {
                    throw new Exception("VerseCode Save Failed: " . json_encode($verseCode->errors));
                }
            }
        } catch (\Exception $e) {
            throw new Exception(
                "Verse upgrade failed (ID: {$verse->id}): " . $e->getMessage() . 
                " [File: " . $e->getFile() . ", Line: " . $e->getLine() . "]",
                0,
                $e
            );
        }
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Total-Count',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Current-Page',
                    'X-Pagination-Per-Page',
                ],
            ],
        ];

        // JWT 认证 - 升级接口需要管理员权限
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['options'],
                ],
                [
                    'allow' => true,
                    'actions' => ['upgrade', 'take-photo', 'test'],
                    'roles' => ['@'], // 需要登录用户
                ],
            ],
        ];

        return $behaviors;
    }


    public function actionTakePhoto($verse_id)
    {
        $snapshot = Snapshot::CreateById($verse_id);
        if ($snapshot->validate()) {
            $snapshot->save();
        } else {
            throw new Exception(json_encode($snapshot->errors), 400);
        }
        return $snapshot->toArray([], ['code', 'id', 'name', 'data', 'description', 'metas', 'resources', 'uuid', 'image']);
    }

    /**
     * 将指定 key 的 tag 迁移到 property，并为对应的 verse 创建 verse_property 关联
     * @param string $key 标签的 key
     * @return array 迁移结果
     * @throws Exception
     */
    private function migrateTagToProperty($key)
    {
        try {
            // 查找指定 key 的 tag
            $tag = \api\modules\v1\models\Tags::findOne(['key' => $key]);

            if (!$tag) {
                return [
                    'status' => 'skipped',
                    'key' => $key,
                    'message' => "Tag with key \"{$key}\" not found",
                ];
            }

            // 检查 Property 表是否有对应 key 的记录，如果没有则创建
            $property = \api\modules\v1\models\Property::findOne(['key' => $key]);
            $propertyCreated = false;

            if (!$property) {
                $property = new \api\modules\v1\models\Property();
                $property->key = $key;
                $property->info = $tag->name;
                if (!$property->save()) {
                    throw new Exception("Failed to create Property for key '{$key}': " . json_encode($property->errors));
                }
                $propertyCreated = true;
            }

            // 通过 verse_tags 表查找所有带有该标签的 verse
            $verses = Verse::find()
                ->innerJoin('verse_tags', 'verse_tags.verse_id = verse.id')
                ->where(['verse_tags.tags_id' => $tag->id])
                ->all();

            $verseList = [];
            $versePropertiesCreated = 0;
            $errors = [];

            foreach ($verses as $verse) {
                try {
                    // 检查该 verse 是否已有对应的 verse_property
                    $existingVerseProperty = \api\modules\v1\models\VerseProperty::findOne([
                        'verse_id' => $verse->id,
                        'property_id' => $property->id,
                    ]);

                    if (!$existingVerseProperty) {
                        // 创建新的 verse_property 记录
                        $verseProperty = new \api\modules\v1\models\VerseProperty();
                        $verseProperty->verse_id = $verse->id;
                        $verseProperty->property_id = $property->id;
                        if (!$verseProperty->save()) {
                            throw new Exception("Failed to save VerseProperty: " . json_encode($verseProperty->errors));
                        }
                        $versePropertiesCreated++;
                    }

                    $verseList[] = [
                        'id' => $verse->id,
                        'name' => $verse->name,
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'verse_id' => $verse->id,
                        'error' => $e->getMessage(),
                    ];
                    Yii::error("Tag migration error for verse {$verse->id}: " . $e->getMessage(), 'upgrade');
                }
            }

            return [
                'status' => 'success',
                'key' => $key,
                'property_created' => $propertyCreated,
                'count' => count($verseList),
                'verse_properties_created' => $versePropertiesCreated,
                'verses' => $verseList,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            throw new Exception(
                "Tag migration failed for key '{$key}': " . $e->getMessage() . 
                " [File: " . $e->getFile() . ", Line: " . $e->getLine() . "]",
                0,
                $e
            );
        }
    }
}

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
        // 防止执行超时
        set_time_limit(0);
        ignore_user_abort(true);

        
        // 增加内存限制，防止大量数据处理导致内存溢出
        ini_set('memory_limit', '512M');
        
        Version::upgrade();

         
        $this->migrateTagToProperty('public');
        $this->migrateTagToProperty('checkin');
        

        $result = [
            'meta' => ['total' => 0, 'success' => 0, 'fail' => 0, 'errors' => []],
            'verse' => ['total' => 0, 'success' => 0, 'fail' => 0, 'errors' => []],
        ];

        // 批量处理 Meta，使用 batch/each 减少内存占用
        // 每次处理 100 条
        foreach (Meta::find()->each(100) as $meta) {
            $result['meta']['total']++;
            try {
                $this->upgradeMeta($meta);
                $result['meta']['success']++;
            } catch (\Exception $e) {
                $result['meta']['fail']++;
                // 记录错误但不中断流程
                $result['meta']['errors'][] = "Meta ID {$meta->id}: " . $e->getMessage();
                Yii::error("Meta Upgrade Error ID {$meta->id}: " . $e->getMessage(), 'upgrade');
            }
        }

        // 批量处理 Verse
        foreach (Verse::find()->each(100) as $verse) {
            $result['verse']['total']++;
            try {
                $this->upgradeVerse($verse);
                $result['verse']['success']++;
            } catch (\Exception $e) {
                $result['verse']['fail']++;
                $result['verse']['errors'][] = "Verse ID {$verse->id}: " . $e->getMessage();
                Yii::error("Verse Upgrade Error ID {$verse->id}: " . $e->getMessage(), 'upgrade');
            }
        }

        return $result;
    }

    /**
     * 升级 Meta 数据
     * code里面的内容要放在metacode 里面的 lua 和js
     * @param Meta $meta
     */
    private function upgradeMeta($meta)
    {
        // 1. 确保 UUID 存在
        if (empty($meta->uuid)) {
            $meta->uuid = \Faker\Provider\Uuid::uuid();
            $meta->save();
        }

        // 2. 刷新资源关联
        $meta->refreshResources();

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
    }

    /**
     * 升级 Verse 数据
     * code里面的内容要放在versecode 里面的 lua 和js
     * @param Verse $verse
     */
    private function upgradeVerse($verse)
    {
        // 1. 确保 UUID 存在
        if (empty($verse->uuid)) {
            $verse->uuid = \Faker\Provider\Uuid::uuid();
            $verse->save();
        }

        // 2. 刷新 Meta 关联
        $verse->refreshMetas();

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
                    'actions' => ['upgrade', 'take-photo'],
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
     */
    private function migrateTagToProperty($key)
    {
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
            $property->save();
            $propertyCreated = true;
        }
        
        // 通过 verse_tags 表查找所有带有该标签的 verse
        $verses = Verse::find()
            ->innerJoin('verse_tags', 'verse_tags.verse_id = verse.id')
            ->where(['verse_tags.tags_id' => $tag->id])
            ->all();
        
        $verseList = [];
        $versePropertiesCreated = 0;
        
        foreach ($verses as $verse) {
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
                $verseProperty->save();
                $versePropertiesCreated++;
            }
            
            $verseList[] = [
                'id' => $verse->id,
                'name' => $verse->name,
            ];
        }
        
        return [
            'status' => 'success',
            'key' => $key,
            'property_created' => $propertyCreated,
            'count' => count($verseList),
            'verse_properties_created' => $versePropertiesCreated,
            'verses' => $verseList,
        ];
    }
}

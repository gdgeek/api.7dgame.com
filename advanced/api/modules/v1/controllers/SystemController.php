<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use api\modules\v1\models\Snapshot;
use api\modules\v1\models\Verse;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;

use yii\base\Exception;

use api\modules\v1\models\data\VerseCodeTool;
use api\modules\v1\models\data\MetaCodeTool;
class SystemController extends Controller
{
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
        /*
                $behaviors['authenticator'] = [
                    'class' => CompositeAuth::class,
                    'authMethods' => [
                        JwtHttpBearerAuth::class,
                    ],
                    'except' => ['options'],
                ];

                $behaviors['access'] = [
                    'class' => AccessControl::class,
                ];*/
        return $behaviors;
    }
/*
    public function actionVerseCode($verse_id)
    {
        $post = Yii::$app->request->post();
        $model = new VerseCodeTool($verse_id);
        $model->load($post, '');
        if ($model->validate()) {
            $model->save();
        } else {
            throw new Exception(json_encode($model->errors), 400);
        }
        return $model;
    }

    public function actionMetaCode($meta_id)
    {
       
        $model = new MetaCodeTool($meta_id);
        $post = Yii::$app->request->post();
        $model->load($post, '');
        
        if ($model->validate()) {
            $model->save();
        } else {
            throw new Exception(json_encode($model->errors), 400);
        }
        return $model;
    }*/
    public function actionVerse($verse_id, $test = true)
    {
        $verse = \api\modules\private\models\Verse::findOne($verse_id);
        $verse2 = \api\modules\v1\models\VerseSnapshot::findOne($verse_id);
        if (!$verse2) {
            throw new Exception("Verse not found", 404);
        }
        if ($test) {
            return $verse2->toArray([], ['code', 'id', 'name', 'data', 'description', 'metas', 'resources', 'uuid', 'image', 'managers']);
        } else {
            return $verse->toArray([], ['code', 'id', 'name', 'data', 'description', 'metas', 'resources', 'uuid', 'image', 'managers']);
        }

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
    public function actionUpgrade()
    {
        set_time_limit(0);
        
        $publicResult = $this->migrateTagToProperty('public');
        $checkinResult = $this->migrateTagToProperty('checkin');
        
        return [
            'public' => $publicResult,
            'checkin' => $checkinResult,
        ];
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
            throw new Exception("Tag with key \"{$key}\" not found", 404);
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

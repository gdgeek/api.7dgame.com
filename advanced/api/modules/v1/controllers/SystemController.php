<?php
namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use api\modules\v1\models\Snapshot;
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

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];
        return $behaviors;
    }

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
    }
    public function actionVerse($verse_id)
    {
        $verse = \api\modules\private\models\Verse::findOne($verse_id);
        if (!$verse) {
            throw new Exception("Verse not found", 404);
        }
        return $verse->toArray([], ['code', 'id', 'name', 'data', 'description', 'metas', 'resources', 'uuid', 'image']);
    }

    public function actionTakePhoto($verse_id)
    {
        
        $snapshot = Snapshot::CreateById($verse_id);
        if ($snapshot->validate()) {
            $snapshot->save();
        } else {
            throw new Exception(json_encode($snapshot->errors), 400);
        }
        return $snapshot->toArray([],['code','id','name','data','description','metas','resources','uuid','image']);
    }
}

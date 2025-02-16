<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\ResourceSearch;
use api\modules\v1\models\Resource;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\helpers\HtmlPurifier;
use yii\rest\ActiveController;

use yii\caching\TagDependency;
class ResourceController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Resource';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
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
            'class' => CompositeAuth::className(),
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

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        return $actions;
    }
    
    public function actionView($id)
    {
        return Resource::find()->where(['id'=>$id])->cache(3600, new TagDependency(['tags' => 'resource_cache']))->one();
    }
    public function actionIndex()
    {
        $searchModel = new ResourceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        if (isset(Yii::$app->request->queryParams['type'])) {
            $type = HtmlPurifier::process(Yii::$app->request->queryParams['type']);
            $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id, 'type' => $type])->cache(3600, new TagDependency(['tags' => 'resource_cache']));
        } else {
            $dataProvider->query->andWhere(['author_id' => Yii::$app->user->id])->cache(3600, new TagDependency(['tags' => 'resource_cache']));
        }
        
        return $dataProvider;
    }

}

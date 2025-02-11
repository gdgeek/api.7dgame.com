<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\ResourceSearch;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\helpers\HtmlPurifier;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;

class MetaResourceController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\MetaResource';
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

        // unset($behaviors['authenticator']);
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

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    public function actionResources()
    {

        if (!isset(Yii::$app->request->queryParams['type'])) {
            throw new BadRequestHttpException('缺乏 type 数据');
        }

        if (!isset(Yii::$app->request->queryParams['meta_id'])) {
            throw new BadRequestHttpException('缺乏 meta_id 数据');
        }
        $searchModel = new ResourceSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        $type = HtmlPurifier::process(Yii::$app->request->queryParams['type']);
        $meta_id = HtmlPurifier::process(Yii::$app->request->queryParams['meta_id']);
        $query = $dataProvider->query;
        $query->select('resource.*')->leftJoin('meta_resource', '`meta_resource`.`resource_id` = `resource`.`id`')->andWhere(['meta_resource.meta_id' => $meta_id, 'resource.type' => $type]);
        return $dataProvider;
    }

}

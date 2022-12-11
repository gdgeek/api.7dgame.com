<?php
namespace api\modules\v1\controllers;

use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class MetaEventController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\MetaEvent';
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
/*
public function actions()
{
$actions = parent::actions();
unset($actions['index']);
return $actions;
}

public function actionIndex()
{

$searchModel = new MetaSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams);

$dataProvider->query->andWhere(['author_id' => Yii::$app->user->id]);

return $dataProvider;
}
 */
}

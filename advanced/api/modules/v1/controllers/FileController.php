<?php
namespace api\modules\v1\controllers;

use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class FileController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\File';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

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

    public function actionIndex()
    {
        $activeData = new ActiveDataProvider([
            'query' => \common\models\Project::find()->where(['user_id' => Yii::$app->user->id]),
            'pagination' => false,
        ]);
        return $activeData;
    }
}

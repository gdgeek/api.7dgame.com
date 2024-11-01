<?php
namespace api\modules\a1\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;

class VpScoreController extends ActiveController
{

    public $modelClass = 'api\modules\a1\models\VpScore';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

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

        return $behaviors;
    }
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['options']);
        unset($actions['view']);
        return $actions;
    }
    public function actionCreate()
    {
        $model = new $this->modelClass();
        $model->load(\Yii::$app->request->post(), '');
        if ($model->save()) {
            return $model;
        } else {
            return $model->errors;
        }
    }
}

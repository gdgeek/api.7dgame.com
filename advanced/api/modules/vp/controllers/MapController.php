<?php
namespace api\modules\vp\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use api\modules\vp\helper\KeyTokenAuth;
use api\modules\vp\models\Guide;

class MapController extends ActiveController
{

    public $modelClass = 'api\modules\vp\models\Map';
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

        $behaviors['authenticator'] = [
            'class' => KeyTokenAuth::className(),
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
    public function actionIndex(){

        $papeSize = \Yii::$app->request->get('pageSize', 1);
        $data = new ActiveDataProvider([
            'query' => \api\modules\vp\models\Map::find(),
            'pagination' => [
                'pageSize' => $papeSize,
            ]
        ]);
        return $data;
    }
    public function actionPage($page){
        
        $model = \api\modules\vp\models\Map::find()->where(['page' => $page])->one();
        return $model;
    }
    public function actionSetup(){
        return \Yii::$app->player->setup();
    }
   
}

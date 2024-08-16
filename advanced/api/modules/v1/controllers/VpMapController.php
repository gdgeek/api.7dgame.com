<?php
namespace api\modules\v1\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use api\modules\v1\models\VpGuide;
use Yii;
use yii\helpers\HtmlPurifier;
use yii\web\BadRequestHttpException;
use yii\filters\auth\CompositeAuth;


use mdm\admin\components\AccessControl;

use api\modules\v1\models\SpaceSearch;
use bizley\jwt\JwtHttpBearerAuth;
class VpMapController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\VpMap';
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
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
       // unset($actions['create']);
        unset($actions['update']);
        //unset($actions['delete']);
        unset($actions['options']);
        unset($actions['view']);
        return $actions;
    }
    public function actionIndex(){

        $papeSize = \Yii::$app->request->get('pageSize', 1);
        $data = new ActiveDataProvider([
            'query' => \api\modules\v1\models\VpMap::find(),
            'pagination' => [
                'pageSize' => $papeSize,
            ]
        ]);
        return $data;
    }
    public function actionPage($page){
        
        $model = \api\modules\v1\models\VpMap::find()->where(['page' => $page])->one();
        return $model;
    }
    public function actionSetup(){
        return \Yii::$app->player->setup();
    }
   
}

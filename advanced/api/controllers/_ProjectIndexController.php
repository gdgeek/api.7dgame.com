<?php
namespace api\controllers;
use Yii;
use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\filters\Cors;
use yii\data\ActiveDataProvider;

use yii\filters\auth\CompositeAuth;

use sizeg\jwt\JwtHttpBearerAuth;

use common\models\Project;
use common\models\Programme;
use api\modules\v1\models\Resource;

class ProjectIndexController extends ActiveController
{
    public $modelClass = 'common\models\Project';
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
    
        // remove authentication filter
      
        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
        'class' => CompositeAuth::className(),
        'authMethods' => [
            JwtHttpBearerAuth::class,
            ],
        ];
        $auth = $behaviors['authenticator'];
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];
        
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
    
        return $behaviors;
    }
    /*
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }*/
    public function actions()
    {
        $actions = parent::actions();

        // 禁用 "delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['post'], $actions['get'], $actions['index']);
        return $actions;
    }
    
    public function actionIndex() {
        $activeData = new ActiveDataProvider([
            'query' => \common\models\Project::find()->where(['user_id' =>  Yii::$app->user->id]),
            'pagination' => false
        ]);
        return $activeData;
    }

}

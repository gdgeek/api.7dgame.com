<?php
namespace api\controllers;

use Yii;
use yii\rest\ActiveController;

use yii\filters\auth\CompositeAuth;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class PeopleController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\User';
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();
    
        // remove authentication filter
      
        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
        'class' => CompositeAuth::class,
        'authMethods' => [
            JwtHttpBearerAuth::class,
            ],
        ];
        $auth = $behaviors['authenticator'];
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
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

    public function actionIndex()
    {
        $params = Yii::$app->request->bodyParams;
        $ret = new \stdClass();
        $ret->name = "succeed";
        $ret->message = "Query user information";
        $ret->code = 0;
        $ret->status = 200;

        $people = new \stdClass();
        $people->username = Yii::$app->user->identity->username;
        $people->email = Yii::$app->user->identity->email;
      //  $people->access_token = Yii::$app->user->identity->access_token;
        $ret->data = $people;
        return $ret;
    }

    
   
}

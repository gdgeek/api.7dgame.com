<?php

namespace api\modules\v1\controllers;

use api\common\models\UserDataForm;
use api\modules\v1\models\User;
use api\modules\v1\models\UserCreation;
use mdm\admin\components\AccessControl;
use mdm\admin\components\Configs;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;

class UserController extends \yii\rest\Controller

{

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
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];
        // re-add authentication filter

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;
    }
    /*
    private function getAssignments($userId)
    {
        $manager = Configs::authManager();
        $assignments = $manager->getAssignments($userId);
        $ret = [];
        foreach ($assignments as $key => $value) {
            $ret[] = $value->roleName;

        }
        return $ret;
    }*/
    private function getUserData()
    {
    //    $user = new \stdClass();
        $user =  Yii::$app->user->identity;
       // $user->username = Yii::$app->user->identity->username;
       // $user->data = Yii::$app->user->identity->getData();
       // $user->roles = $this->getAssignments(Yii::$app->user->identity->id);
        return [
            'username' => $user->username,
            'data' => $user->getData(),
            'roles' => $user->roles,
        ];

    }
    public function actionCreation()
    {
        $creation = new UserCreation();
        return $creation;
    }
    public function actionSetData()
    {
        $model = new UserDataForm(Yii::$app->user->identity);
        $post = Yii::$app->request->post();
        if ($model->load($post, '') && $model->save()) {
            return $this->getUserData();
        } else {
            if (count($model->errors) == 0) {
                throw new Exception("缺少数据", 400);
            } else {
                throw new Exception(json_encode($model->errors), 400);
            }

        }
    }
    
    public function actionInfo()
    {
        return $this->getUserData();
    }

}

<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use Yii;

class AuthController extends \yii\rest\Controller
{

   // public $modelClass = 'app\modules\v1\models\Player';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors;
    }
    public function actionLogin(){
        $username = Yii::$app->request->post("username");
        if (!$username) {
            throw new BadRequestHttpException("username is required");
        }
        $password = Yii::$app->request->post("password");
        if (!$password) {
            throw new BadRequestHttpException("password is required");
        }

        $user = User::findByUsername($username);
        if(!$user){
            throw new BadRequestHttpException("no user");
        }
        if(!$user->validatePassword($password)){
            throw new BadRequestHttpException("wrong password");
        }

       
        return ['success' => true, 'message' => "login", 'token'=> $user->token()];
    }


}
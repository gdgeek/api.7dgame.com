<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\Wechat;
use api\modules\v1\models\User;
use Yii;

class WechatController extends \yii\rest\Controller
{

   // public $modelClass = 'app\modules\v1\models\Player';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors;
    }
    public function actionLogin(){
        $token = Yii::$app->request->post("token");
        if (!$token) {
            throw new BadRequestHttpException("token is required");
        }
        $wechat = Wechat::findOne(['token'=>$token]);
        if (!$wechat) {
            throw new BadRequestHttpException("no wechat");

        }
        if($wechat->user){
            return ['success' => true, 'message' => "login", 'token'=> $wechat->user->token()];
        }else{
            throw new BadRequestHttpException('Login failed');
        }
    }

    public function actionRegister(){
        $token = Yii::$app->request->post("token");
        if (!$token) {
            throw new BadRequestHttpException("token is required");
        }
        $username = Yii::$app->request->post("username");
        if (!$username) {
            throw new BadRequestHttpException("username is required");
        }
        $password = Yii::$app->request->post("password");
        if (!$password) {
            throw new BadRequestHttpException("password is required");
        }

        $wechat = Wechat::findOne(['token'=>$token]);
        if (!$wechat) {
            throw new BadRequestHttpException("no wechat");
        }
        if($wechat->user){

            throw new BadRequestHttpException("already registered,". $wechat->user->id);
        }else{


            $user = User::create($username, $password);
            if(!$user->validate()){
                throw new BadRequestHttpException(json_encode($user->errors));
            }
            $user->save();
            $user->addRoles(["user"]);
           
           if(!$wechat->validate() ){
                throw new BadRequestHttpException(json_encode($wechat->errors, true));
            }
            $wechat->save();
            return ['success' => true, 'message' => "register", 'uid'=>$user->id, 'token'=> $user->token()];
           
        }
    }
  


}
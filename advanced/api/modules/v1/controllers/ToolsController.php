<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\UserCreation;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use Yii;
use api\modules\v1\models\UserLinked;
use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;

class ToolsController extends \yii\rest\Controller

{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

      
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
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
   public function actionUserLinked(){

        $user = Yii::$app->user->identity;
        if(!$user){
            throw new BadRequestHttpException("no user");
        }
        $linked = UserLinked::find()->where(['user_id' => $user->id])->one();
        
        if(!$linked){
            $linked = new UserLinked();
            $linked->user_id = $user->id;
           
        }
        $token = $user->getRefreshToken()->one();
        if(!$token){
            $token = $user->token();
        }
        $linked->key = $token->key;
        if(!$linked->validate()){
            throw new BadRequestHttpException("validate error");
        }
        if(!$linked->save()){
            throw new BadRequestHttpException("save error");
        }
      
        return ['success' => true, 'message' => "user-linked", 'key'=> $linked->key];
       
    }
}

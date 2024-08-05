<?php
namespace api\modules\vp\controllers;
use api\modules\vp\models\Token;
use yii\rest\ActiveController;

class Oauth2Controller extends \yii\rest\Controller{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
       
        return $behaviors;
    }
    
    public function actionAppleIdLogin(){
        $cache = \Yii::$app->cache;
        $cache->set('apple', ["get"=>\Yii::$app->request->get(),"post"=> \Yii::$app->request->post()]);

        return "apple";
    }
    public function actionIndex(){
        $cache = \Yii::$app->cache;
        return $cache->get('apple');
    }
}

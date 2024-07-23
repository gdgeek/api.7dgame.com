<?php
namespace api\modules\vp\controllers;

use yii\rest\ActiveController;

class VerificationController extends \yii\rest\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
       
        return $behaviors;
    }
   
    public function actionCheck(){
        $get = \Yii::$app->request->get();
        return  $get;
    }
}

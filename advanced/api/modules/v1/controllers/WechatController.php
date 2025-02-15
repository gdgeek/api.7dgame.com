<?php

namespace api\modules\v1\controllers;

use Yii;

class WechatController extends \yii\rest\Controller
{

   // public $modelClass = 'app\modules\v1\models\Player';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors;
    }

    public function actionLink()
    {
        return "link";


    }



}
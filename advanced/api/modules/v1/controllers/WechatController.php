<?php

namespace app\modules\v1\controllers;
use Yii;
use yii\rest\ActiveController;
use app\modules\v1\models\OpenId;
use app\modules\v1\models\Wechat;

class WechatController extends \yii\rest\Controller
{

    public $modelClass = 'app\modules\v1\models\Player';
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
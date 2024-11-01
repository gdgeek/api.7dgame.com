<?php
namespace api\modules\v1\controllers;

use yii\rest\ActiveController;

class TestController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Token';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $behaviors;
    }
    public function actionFile()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.bilibili.com/x/space/wbi/arc/search?mid=20959246');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        return $output;
    }

}

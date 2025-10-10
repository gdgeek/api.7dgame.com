<?php
namespace api\modules\v1\controllers;

use api\modules\v1\RefreshToken;
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
    public function actionTest(){
       $all =  RefreshToken::find()->all();
       $one =  RefreshToken::find()->where(['key' => 'KQ5N52i3OAq2jOAL3I0yaMAMCg91PiCb'])->one();

       $one->save();
       return $one;
    }

}

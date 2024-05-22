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
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        $headers = array(
            "'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36'",
        );
        curl_setopt($curl, CURLOPT_HEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

}

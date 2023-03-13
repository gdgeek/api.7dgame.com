<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\File;
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
        $all = File::find()->one();
        return $all;
    }

}

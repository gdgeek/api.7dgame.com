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

        $storage = new \common\components\Storage();
        if ($storage->init()) {

        } else {
            return $storage->errors;
        }

        return $storage->targetDirector('raw', 'test');
    }

}

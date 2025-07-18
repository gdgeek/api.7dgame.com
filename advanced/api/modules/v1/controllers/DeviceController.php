<?php

namespace api\modules\v1\controllers;

use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use Yii;

class DeviceController extends \yii\rest\Controller
{

   // public $modelClass = 'app\modules\v1\models\Player';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        return $behaviors;
    }
    public function byUuid()
    {
        $uuid = Yii::$app->request->get("uuid");
        if (!$uuid) {
            throw new BadRequestHttpException("uuid is required");
        }
        $type = Yii::$app->request->get("type");
        if (!$type) {
            throw new BadRequestHttpException("type is required");
        }
        $device = \api\modules\v1\models\Device::findOne(['uuid' => $uuid]);
        if (!$device) {
            throw new BadRequestHttpException("no device");
        }
        return $device;
    }

}
<?php

namespace api\controllers;

use common\components\Storage;
use Yii;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class StorageController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        return $behaviors;
    }

    public function actionFile($bucket, $key)
    {
        $storage = new Storage();
        $storage->init();

        if (!in_array($bucket, $storage->publicBuckets, true)) {
            throw new ForbiddenHttpException('该存储桶不支持公开访问');
        }

        $path = $storage->path($bucket, $key);
        if (!is_file($path)) {
            throw new NotFoundHttpException('文件不存在');
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        return Yii::$app->response->sendFile($path, basename($path), ['inline' => true]);
    }
}

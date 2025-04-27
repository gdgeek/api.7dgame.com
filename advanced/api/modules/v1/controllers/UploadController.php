<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\UserCreation;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class UploadController extends \yii\rest\Controller

{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

      
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];
        // re-add authentication filter

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        return $behaviors;
    }
    public function actionCreation()
    {
        $creation = new UserCreation();
        return $creation;
    }

    public function actionFile()
    {

        $storage = new \common\components\Storage();
        if (!$storage->init()) {
            throw new Exception('文件系统初始化失败', 400);
        }
        $data = \Yii::$app->request->post();

        $skip = ArrayHelper::getValue($data, 'skip'); //['skip'];
        $filename = ArrayHelper::getValue($data, 'filename'); //['skip'];
        $size = ArrayHelper::getValue($data, 'size'); //['skip'];
        $upload_size = ArrayHelper::getValue($data, 'upload_size'); //['skip'];
        $md5 = ArrayHelper::getValue($data, 'md5'); //['skip'];
        $directory = ArrayHelper::getValue($data, 'directory'); //['skip'];
        $bucket = ArrayHelper::getValue($data, 'bucket'); //['skip'];

        $file = UploadedFile::getInstanceByName('file');

        if ($file == null) {
            throw new Exception('没有上传文件', 400);
        }

        $path = $storage->tempDirector . $filename;
        if ($skip == 0) {
            $file->saveAs($path);
        } else {
            file_put_contents($path, file_get_contents($file->tempName), FILE_APPEND);
        }

        if ($size <= $upload_size) {
            if ($md5 == md5_file($path)) {
                rename($path, $storage->targetDirector($bucket, $directory) . $filename);
                return json_encode(['target' => $storage->targetDirector($bucket, $directory), 'over' => true, 'data' => $data]);
            }
        }

        return json_encode(['over' => false, 'data' => $data]);

    }

}

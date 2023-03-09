<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\UserCreation;
use mdm\admin\components\AccessControl;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class UploadController extends \yii\rest\Controller

{

    public function behaviors()
    {

        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
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
    public function actionTest()
    {
        return 123;
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

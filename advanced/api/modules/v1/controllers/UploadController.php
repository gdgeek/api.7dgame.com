<?php

namespace api\modules\v1\controllers;

use yii\web\UploadedFile;

class UploadController extends \yii\rest\Controller
{

    public function behaviors()
    {

        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        return $behaviors;
    }

    public function actionFile($advanced = false)
    {
        $data = \Yii::$app->request->post();
        $file = UploadedFile::getInstanceByName('file');

        $path = 'store/temp/' . "_" . $data['filename'];
        if ($data['skip'] == 0) {
            $file->saveAs($path);
        } else {
            file_put_contents($path, file_get_contents($file->tempName), FILE_APPEND);
        }
        if ($data['size'] <= $data['upload_size']) {
            $md5 = md5_file($path);
            if ($md5 == $data['md5']) {
                $target = $advanced ? 'store/advanced/from/' : 'store/files/';
                rename($path, $target . $data['filename']);
                return json_encode(['target' => $target, 'over' => true, 'data' => $data, 'advanced' => $advanced]);
            }
        }

        return json_encode(['over' => false, 'data' => $data, 'advanced' => $advanced]);
    }

}

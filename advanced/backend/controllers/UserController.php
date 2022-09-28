<?php

namespace backend\controllers;

use yii\web\UploadedFile;

class FileController extends \yii\web\Controller

{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {

        \Yii::$app->db->createCommand()->truncateTable('file_store')->execute();

        $store = \Yii::$app->store;
        $client = $store->getClient();

        $maker = null;
        do {

            $result = $client->listObjects(array(
                'Bucket' => $store->bucket, //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                'Delimiter' => '/', //Delimiter表示分隔符, 设置为/表示列出当前目录下的object, 设置为空表示列出所有的object
                'EncodingType' => 'url', //编码格式，对应请求中的 encoding-type 参数
                'Marker' => $maker, //起始对象键标记
                'MaxKeys' => 500, // 设置最大遍历出多少个对象, 一次listObjects最大支持1000
            ));
            $maker = $result['NextMarker'];
            $this->addContents($result['Contents']);

        } while ($maker != null);

        return $this->render('index');

    }
    public function addContents($contents)
    {

        $values = array();

        foreach ($contents as $item) {
            array_push($values, [$item['Key'], $item['Size']]);
        }

        \Yii::$app->db->createCommand()->batchInsert('file_store', ['key', 'size'], $values)->execute();
        $sql = "SELECT * FROM `file_store` WHERE (select count(1) as num from `file` where `file`.key = `file_store`.key) = 0";
        $result = \Yii::$app->db->createCommand($sql)->queryAll();

        echo json_encode($result);
//
    }
    public function actionFile()
    {

        $data = \Yii::$app->request->post();
        $file = UploadedFile::getInstanceByName('file');

        $path = 'files/upload/' . "_" . $data['filename'];
        if ($data['skip'] == 0) {
            $file->saveAs($path);
        } else {
            file_put_contents($path, file_get_contents($file->tempName), FILE_APPEND);
        }
        if ($data['size'] <= $data['upload_size']) {
            $md5 = md5_file($path);
            if ($md5 == $data['md5']) {
                rename($path, 'files/upload/' . $data['filename']);
                return "cool";
            }
        }

        return json_encode($data);
    }

    public function actionUpload()
    {
        $data = \Yii::$app->request->post();
        $file = UploadedFile::getInstanceByName('file');
        $path = 'files/upload/' . "_" . $data['md5'];
        if ($data['skip'] == 0) {
            $file->saveAs($path);
        } else {
            file_put_contents($path, file_get_contents($file->tempName), FILE_APPEND);
        }

        if ($data['size'] <= $data['upload_size']) {
            $md5 = md5_file($path);
            if ($md5 == $data['md5']) {
                rename($path, 'files/upload/' . $data['filename']);
                return "cool";
            }
        }
        echo json_encode($data);
    }

}
//select * from  B
//2     where (select count(1) as num from A where A.ID = B.ID) = 0

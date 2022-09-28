<?php

namespace backend\assets;
use Yii;
use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class FileAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';


    public $js = [
        'public/libs/file/file.js',
        'public/libs/jszip/jszip.js',
        'public/libs/md5/spark-md5.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];


    
    public function init()
    {
        if(Yii::$app->params['information']['disk']){
            array_push($this->js, 'public/libs/file/file-local.js');
        }else{

            array_push($this->js, 'public/libs/file/cos-js-sdk-v5.min.js');
            array_push($this->js, 'public/libs/file/file-cos.js');

        }
        parent::init();
    }
}

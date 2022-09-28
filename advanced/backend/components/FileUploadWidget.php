<?php

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class FileUploadWidget extends Widget
{
    public $model;
    public $bucket;
    public $region;
    public $button;
    public $fileType;
    public $compressed;

    public function init()
    {
        parent::init();
        if ($this->bucket === null) {
            $this->bucket = 'files-1257979353';
        } 
        if ($this->region === null) {
            $this->region = 'ap-chengdu';
        } 
        
        if ($this->button === null) {
            $this->button = \Yii::t('app', 'Upload');
        } 
        
        if ($this->compressed === null) {
            $this->compressed = false;
        }
        if ($this->fileType === null) {
            $this->fileType = "";
        }
    }

    public function run()
    {     
   
        return $this->render('file_upload', ["model"=>$this->model,
            "bucket" => $this->bucket,
            "region" => $this->region,
            "button" => $this->button,
            "compressed" => $this->compressed,
            "fileType" => $this->fileType,
        
        ]);
    }
}
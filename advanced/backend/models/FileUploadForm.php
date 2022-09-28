<?php

namespace backend\models;
use api\modules\v1\models\File;
use yii\base\Model;
use Yii;

class FileUploadForm extends Model
{


    public $title;
    public $type;
    public $md5;
    public $url;
    public $filename;
    public $compress;

    public function rules()
    {
        return [
            [['title', 'filename', 'type', 'md5', 'url' ], 'required'],
            [['title', 'filename', 'type', 'md5', 'url', 'compress' ], 'string'],
        ];
    }
    public function createFile($user_id){
        $file = new File();
        $file->user_id = $user_id;
        $file->url = $this->url;
        $file->filename = $this->filename;
        $file->type = $this->type;
        $file->md5 = $this->md5;
        return $file;

    }
	public function attributeLabels()
    {
        return [
            'title' => Yii::t('app', 'title'),
            'filename' => Yii::t('app', 'filename'),
            'type' => Yii::t('app', 'type'),
            'md5' => Yii::t('app', 'md5'),
            'url' => Yii::t('app', 'url'),
            'compress' => Yii::t('app', 'compress'),
        ];
    }

}
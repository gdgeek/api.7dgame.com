<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $filename;
    public $title;
    public $sharing;

    public function rules()
    {
        return [
		
            [['title','filename'], 'required'],
            [['filename'], 'string'],
            [['sharing'], 'boolean'],
        ];
    }

	public function attributeLabels()
    {
        return [
            'title' => '标题',
            'filename' => '文件名',
            'sharing' => '是否公开',
        ];
    }



    /*
    public function upload()
    {
        if ($this->validate()) {
            $this->meshFile->saveAs('uploads/' . $this->meshFile->baseName . '.' . $this->meshFile->extension);
            return true;
        } else {
            return false;
        }
    }*/
}
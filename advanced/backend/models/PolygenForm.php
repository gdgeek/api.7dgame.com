<?php

namespace backend\models;
use yii\base\Model;
use Yii;
class PolygenForm extends Model
{
    public $title;
    public $type;
    public $md5;
    public $url;
    public function rules()
    {
        return [
		
            [['title','type','md5','url' ], 'required'],
            [['title','type','md5','url' ], 'string'],
        ];
    }

	public function attributeLabels()
    {
        return [
            'title' => Yii::t('app', 'title'),
            'type' => Yii::t('app', 'type'),
            'md5' => Yii::t('app', 'md5'),
            'url' => Yii::t('app', 'url'),
        ];
    }

}
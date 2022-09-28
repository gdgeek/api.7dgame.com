<?php

namespace backend\models;

use yii\base\Model;
use api\modules\v1\models\File;
use yii\web\UploadedFile;
use Yii;

class MaterialForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $select_material;//ѡ���Ѿ������Ĳ���
    public $name;
    public $albedo;
    public $metallic;
    public $normal;
    public $occlusion;
    public $emission;
    public $color;
    public $alpha;
    public $smoothness;

    public function rules()
    {
        return [
            [['color', 'name', 'albedo', 'metallic', 'normal', 'occlusion', 'emission'], 'string', 'max' => 255],
            [['smoothness'], 'double'],
            [['alpha'], 'double'],
        ];
    }

    public function getFile($key)
    {
        $file = new File();
        $ary = array();
        $ary["File"] = json_decode($this->$key, true);
        $file->load($ary);
        return $file;
    }

    public function toJson($data)
    {
        $d = new \stdClass();
        $d->id = $data->id;
        $d->url = $data->url;
        $d->md5 = $data->md5;
        $d->user_id = $data->user_id;
        return json_encode($d);
    }

    public function readOne($material, $key)
    {
        $func = 'get' . ucfirst($key) . '0';
        $ret = $material->$func()->one();
        if ($ret) {
            return $this->toJson($ret);
        }
        return null;
    }

    public function read($material)
    {
        if ($material != null) {
            $this->albedo = $this->readOne($material, 'albedo');
            $this->metallic = $this->readOne($material, 'metallic');
            $this->normal = $this->readOne($material, 'normal');
            $this->occlusion = $this->readOne($material, 'occlusion');
            $this->emission = $this->readOne($material, 'emission');
            $this->name = $material->name;
            $this->color = $material->color;
            $this->smoothness = $material->smoothness;
            $this->alpha = $material->alpha;
        }
    }

    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'name'),
            'albedo' => Yii::t('app', 'albedo'),
            'metallic' => Yii::t('app', 'metallic'),
            'normal' => Yii::t('app', 'normal'),
            'occlusion' => Yii::t('app', 'occlusion'),
            'emission' => Yii::t('app', 'emission'),
        ];
    }
}
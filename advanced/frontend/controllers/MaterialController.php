<?php

namespace frontend\controllers;
use common\models\Material;
use api\modules\v1\models\Resource;
use yii\helpers\Url;
class MaterialController extends \yii\web\Controller
{
    private function readTexture($material, $key){

        $ret = null;
        $func = 'get'.ucfirst($key).'0';

        $file = $material->$func()->one();

        if($file){
            $ret = new \stdClass();
            $ret->md5 = $file->md5;
            $ret->url = $file->url;
            $ret->type = $file->type;
        }
        return $ret;
    }
    private function readMaterials($materials){
        $ret = array();
        foreach($materials as $val){

            array_push($ret , $this->readMaterial($val));
        }
        return $ret;

    }
    private function readMaterial($material){
        $data = new \stdClass();
        $data->name = $material->name;
        $data->color = $material->color;
        $data->smoothness = $material->smoothness;
        $data->alpha = $material->alpha;
        $data->albedo = $this->readTexture($material, 'albedo');
        $data->emission = $this->readTexture($material, 'emission');
        $data->metallic = $this->readTexture($material, 'metallic');
        $data->normal= $this->readTexture($material, 'normal');
        $data->occlusion  = $this->readTexture($material, 'occlusion');
        return $data;
    }

    public function actionFile($id, $filename){
        if($filename == 'list.mrpp'){
            $polygen = Resource::findOne(['id'=>$node->data->mesh, 'type'=>'polygen']);
            if($polygen != null){

                $materials = $polygen->getMaterials()->all();
                $array = array();
                foreach($materials as $m){
                    array_push($array, $this->readMaterial($m));
                }
                $ret = new \stdClass();
                $ret->list = $array;
                echo json_encode($ret, JSON_UNESCAPED_SLASHES);
            }else{
                return json_encode(null);
            }
        }
    }

/*
    public function actionFile($id, $filename){

        if($filename == 'json.mrpp'){
            $material = Material::findOne($id);
            $data = new \stdClass();
            if($material){
                return json_encode($this->readMaterial($material), JSON_UNESCAPED_SLASHES);
            }else{
                return json_encode(null);
            }
        }else{

            return json_encode(null);
        }
    }*/
    public function actionIndex()
    {
       echo Url::home('http');
       // return $this->renderPartial('index');
    }

}

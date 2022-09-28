<?php

namespace common\components\editor;
use common\components\Vector3Data;
use yii\helpers\Url;
use api\modules\v1\models\Resource;

class NodePolygen extends Point
{
    public function setup($node, $inputs, $reader, $map, $node_id)
    {

        parent::setup($node, $inputs, $reader, $map, $node_id);

        // $this->id = $node->id;
        $this->type = "polygen";

        $polygen = Resource::findOne(['id'=>$node->data->mesh, 'type'=>'polygen']);
        if (!isset($polygen)) {
            return;
        }


        $data = new \stdClass();
        $types = explode(".", $polygen->file->type);
    
        $data->type = 'glb';//$polygen->file->type;//empty($types[0]) ? $types[1] : $types[0];
        if (isset($inputs["action"][0])) {
            $data->action = $inputs["action"][0];
        }
        $data->file = new \stdClass();

        $info = json_decode($polygen->info);
        $data->info = $polygen->info;
        if(isset($info) && isset($info->size)){
            $data->size = new Vector3Data(floatval($info->size->x), floatval($info->size->y), floatval($info->size->z));
        }else{
            $data->size = new Vector3Data(0.1, 0.1, 0.1);
        }
        if(isset($info) && isset($info->center)){
            $data->center = new Vector3Data(floatval($info->center->x), floatval($info->center->y), floatval($info->center->z));
        }else{
            $data->center = new Vector3Data(0, 0, 0);
        }
        $data->file->url = $polygen->file->url;
        $data->file->md5 = $polygen->file->md5;
        $data->file->type = 'glb';//$polygen->file->type;

        $this->data = json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

        return;
    }



    public static function Data()
    {

        return  [
            'name' => 'Polygen',
            'title' => \Yii::t('app/editor', 'Polygen'),
            'type' => [\Yii::t('app/editor', 'Point')],
            'controls' => [
                [
                    'type' => 'string',
                    'name' => 'title',
                    'title' => \Yii::t('app/editor', 'Title'),
                    'default' => 'Title',
                ],
                [
                    'type' => 'resource',
                    'func' => 'setPolygens',
                    'name' => 'mesh',
                    'title' => \Yii::t('app/editor', 'Polygen'),
                    'default' => 'None',
                ],
            ],
            'inputs' => [

                [
                    'name' => 'transform',
                    'title' => \Yii::t('app/editor', 'Local'),
                    'socket' => 'TransformSocket',
                    'multiple' => false,
                ],

                [
                    'name' => 'effects',
                    'title' => \Yii::t('app/editor', 'Effect'),
                    'socket' => 'EffectSocket',
                    'multiple' => true,
                ],
            ],
            'output' => [
                'name' => 'point',
                'title' => \Yii::t('app/editor', 'Point'),
                'socket' => 'PointSocket',

            ],
        ];
    }
}

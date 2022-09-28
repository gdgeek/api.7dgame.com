<?php

namespace common\components\editor;

use common\components\Vector3Data;

class NodeMark{




	public function setup($node, $inputs, $reader, $map, $node_id){
        $this->type = "mark";
        $data = new \stdClass();
        $data->position = new Vector3Data($node->data->position);
        $data->angle = new Vector3Data($node->data->angle);
        $data->index =  $node->data->index;
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
     
	}

    public static function Data(){

        return  [
            'name'=>'Mark',
            'title'=>\Yii::t('app/editor', 'Mark'),

            'type'=>[\Yii::t('app/editor', 'Addon')],
           // 'type'=>\Yii::t('app/editor', 'Addon'),
            'controls'=>[


                [
                    "type"=>"number",
                    'name'=>'index',
                    'title'=>\Yii::t('app/editor', 'Index'),
                    'default'=> 0,
                ],
                [
                    "type"=>"vector3",
                    'name'=>'position',
                    'title'=>\Yii::t('app/editor', 'Position'),
                    'default'=> [0,0,0],
                ],
                [
                    "type"=>"vector3",
                    'name'=>'angle',
                    'title'=>\Yii::t('app/editor', 'Rotate'),
                    'default'=> [0,0,0],
                ],

            ],
            'inputs'=>[
            ],
            'output'=>[
                'name'=>'addon',
                'title'=>\Yii::t('app/addon', 'Addon'),
                'socket'=>'AddonSocket',

            ],
        ];
    }
}

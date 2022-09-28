<?php

namespace common\components\editor;

use common\components\Vector3Data;

class NodeRotate{
	
	
	public function vector3($v3) {
		return new Vector3Data(floatval($v3[0]), floatval($v3[1]), floatval($v3[2]));
	}
	
	public function setup($node, $inputs, $reader, $map){
	
        $this->type = "rotate";
        $data = new \stdClass();
        $data->enabled = true;
        $data->speed = $this->vector3($node->data->speed);
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);


	}

    public static function Data(){
        return  [
            'name'=>'Rotate',
            'title'=>\Yii::t('app/editor', 'Rotate'),
           // 'type'=>\Yii::t('app/editor', 'Effect'),

            'type'=>[\Yii::t('app/editor', 'Effect'),\Yii::t('app/editor', 'Vector3')],
            'controls'=>[
                [
                    'type'=>'vector3',
                    'name' => 'speed',
                    'title' => \Yii::t('app/editor', 'Speed'),

                    'default'=>[0,0,0],
                ],


            ],
            'inputs'=>[

            ],
            'output'=>[
                'name'=>'rotate',
                'title'=>\Yii::t('app/editor', 'Effect'),
                'socket'=>'EffectSocket',

            ],
        ];

    }
}



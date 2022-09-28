<?php

namespace common\components\editor;

use common\components\Vector3Data;

class NodeLocal{


	public function vector3($v3) {
		return new Vector3Data(floatval($v3[0]), floatval($v3[1]), floatval($v3[2]));
	}
	

	public function setup($node, $inputs, $reader, $map){
		
		$this->position = $this->vector3($node->data->position);
		$this->scale = $this->vector3($node->data->scale);
		$this->angle = $this->vector3($node->data->angle);
        if(isset($node->data->active)){
            $this->active = $node->data->active == 0 ? false:true;
        }else{
            $this->active = true;
        }
       
	}

    public static function Data(){
        return  [
            'name'=>'Local',
            'title'=>\Yii::t('app/editor', 'Local'),
            'controls'=>[
                [
                    'type'=>'vector3',
                    'name'=>'position',
                    'title'=>\Yii::t('app/editor', 'Position'),
                    'default'=>[0, 0, 0],
                ],
                [
                    'type'=>'vector3',
                    'name'=>'scale',
                    'title'=>\Yii::t('app/editor', 'Scale'),
                    'default'=>[1,1, 1],
                ],
                [
                    'type'=>'vector3',
                    'name'=>'angle',
                    'title'=>\Yii::t('app/editor', 'Rotate'),
                    'default'=>[0, 0, 0],
                ],

                [
                    'type'=>'bool',
                    'name'=>'active',
                    'title'=>\Yii::t('app/editor', 'Active'),
                    'default'=>true,
                ],


            ],
            'inputs'=>[
                  /*  [
                        'name'=>'effects',
                        'title'=>\Yii::t('app/editor', 'Effect'),
                        'socket'=>'EffectSocket',
                        'multiple' => true,
                    ],*/
                ],
            'output'=>[
                'name'=>'transform',
                'title'=>\Yii::t('app/editor', 'Local'),
                'socket'=>'TransformSocket',

            ],
        ];
    }
}

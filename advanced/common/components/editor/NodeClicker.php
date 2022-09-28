<?php

namespace common\components\editor;


class NodeHint extends Point{




	public function setup($node, $inputs, $reader, $map, $node_id){
        parent::setup($node, $inputs, $reader, $map, $node_id);
        $this->type = "hint";
        
	
        $data = new \stdClass();
		$data->head = $node->data->head;

        
		if(isset($inputs["action"][0])){
			$data->action = $inputs["action"][0];
		}
        $data->lockScale = true;

        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

	}


    public static function Data(){
        return  [
            'name'=>'Hint',
            'title'=>\Yii::t('app/editor', 'Hint'),

            'type'=>[\Yii::t('app/editor', 'Point')],
           // 'type' => 'Point',
            'controls'=>[
                [
                    'type'=>'string',
                    'name'=>"title",
                    'title'=>\Yii::t('app/editor', 'Title'),
                    'default'=>'Title',
                ],
                [
                    'type'=>'string',
                    'name' => 'head',
                    'title' => \Yii::t('app/editor', 'Head'),

                    'default'=>'Head',
                ],

            ],
            'inputs'=>[
                [
                    'name'=>'transform',
                    'title'=>\Yii::t('app/editor', 'Local'),
                    'socket'=>'TransformSocket',
                    'multiple' => false,
                ],
/*
                [
                    'name'=>'action',
                    'title'=>\Yii::t('app/editor', 'Action'),
                    'socket'=>'ActionSocket',
                    'multiple' => false,
                ],   */
                [
                    'name'=>'effects',
                    'title'=>\Yii::t('app/editor', 'Effect'),
                    'socket'=>'EffectSocket',
                    'multiple' => true,
                ],

            ],
            'output'=>[
                'name'=>'point',
                'title'=>\Yii::t('app/editor', 'Point'),
                'socket'=>'PointSocket',

            ],
        ];

    }
}

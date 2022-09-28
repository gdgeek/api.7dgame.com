<?php

namespace common\components\editor;


class NodeBoard extends Point{


	public function setup($node, $inputs, $reader, $map, $node_id){
        parent::setup($node, $inputs, $reader, $map, $node_id);
		
        $this->type = "board";

        
        $data = new \stdClass();
		$data->head = $node->data->head;
		$data->message = $node->data->message;
        $data->lockScale = true;
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
        
		
	}



    public static function Data(){

        return  [
            'name'=>'Board',
            'title'=>\Yii::t('app/editor', 'Board'),

            'type'=>[\Yii::t('app/editor', 'Point')],
           // 'type' => 'Point',
            'controls'=>[
                [
                    'type'=>'string',
                    'name' => 'title',
                    'title' => \Yii::t('app/editor', 'Title'),
                    'default'=>'Title',
                ],
                [
                    'type'=>'string',
                    'name' => 'head',
                    'title' => \Yii::t('app/editor', 'Head'),
                    'default'=>'Head',
                ],
                [
                    'type'=>'string',
                    'name' => 'message',
                    'title' => \Yii::t('app/editor', 'Message'),
                    'default'=>'Message',
                ],

            ],
            'inputs'=>[
                [
                    'name'=>'transform',
                    'title'=>\Yii::t('app/editor', 'Local'),
                    'socket'=>'TransformSocket',
                    'multiple' => false,
                ],   
                [
                    'name'=>'effects',
                    'title'=>\Yii::t('app/editor', 'Effect'),
                    'socket'=>'EffectSocket',
                    'multiple' => true,
                ],

            ],
            'output'=>[
                'name'=>'board',
                'title'=>\Yii::t('app/editor', 'Point'),
                'socket'=>'PointSocket',

            ],
        ];

    }
}

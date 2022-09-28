<?php

namespace common\components\editor;

use common\components\Vector3Data;

class NodeTip extends Point{




	public function setup($node, $inputs, $reader, $map, $node_id){
        parent::setup($node, $inputs, $reader, $map, $node_id);
        $this->type = "tip";
        $data = new \stdClass();
		$data->content = $node->data->content;
        $data->point = new Vector3Data($node->data->point);
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

	}


    public static function Data(){
        return  [
            'name'=>'Tip',
            'title'=>\Yii::t('app/editor', 'Tip'),

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
                    'name' => 'content',
                    'title' => \Yii::t('app/editor', 'Content'),
                    'default'=>'Content',
                ],
                [
                    'type'=>'vector3',
                    'name' => 'point',
                    'title' => \Yii::t('app/editor', 'Point'),
                    'default'=>[0,0.25,0],
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

<?php

namespace common\components\editor;




class NodeNative extends Point{
/*
	public static function GetLabel($node, $node_id){


		$ret = new \stdClass();
		
		$ret->id = $node_id .':'.$node->data->title;
		return $ret;
	}
*/

	public function setup($node, $inputs, $reader, $map, $node_id){
 
        parent::setup($node, $inputs, $reader, $map, $node_id);
       // $this->id = $node->id;
        $this->type = "native";

        $data = new \stdClass();
		$data->type = $node->data->type;
		$data->name = $node->data->name;
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

     
	}

    public static function Data(){

        return  [
            'name'=>'Native',
            'title'=>\Yii::t('app/editor', 'Native'),

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
                    'type'=>'select',
                    'name'=>"type",
                    'title'=>\Yii::t('app/editor', 'Type'),
                    'options' => [
                        ["value" => "music", 'text'=> \Yii::t('app/editor', 'Music')],
                        ["value" => "polygen", 'text'=> \Yii::t('app/editor', 'Polygen')],
                        ["value" => "other", 'text'=> \Yii::t('app/editor', 'Other')],
                    ],
                    'default'=>'polygen',
                ],
                [
                    'type'=>'string',
                    'name'=>"name",
                    'title'=>\Yii::t('app/editor', 'Name'),
                    'default'=>'Name',
                ],

            ],
            'inputs'=>[
                   [
                    'name' => 'transform',
                    'title' => \Yii::t('app/editor', 'Local'),
                    'socket' => 'TransformSocket',
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
                'name'=>'point',
                'title'=>\Yii::t('app/editor', 'Point'),
                'socket'=>'PointSocket',

            ],
        ];


	}
}

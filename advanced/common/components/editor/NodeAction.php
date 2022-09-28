<?php

namespace common\components\editor;


class NodeAction extends Node{


/*
	public static function GetLabel($node, $node_id){
		$ret = new \stdClass();
		$ret->id = $node_id.":".$node->data->id;
		return $ret;
	}*/
	public function setup($node, $inputs, $reader, $map, $node_id, &$c){
		
     //   $this->id = $node->id;
	//	$this->title = Node::GetTitle($node, $node_id);//$node_id.":".$node->data->id;
	//	$this->json = $node->data->json;
	//	$this->enabled =true;


        $this->id = $node->id;
        $this->type = "action";
        $this->title = Node::GetTitle($node, $node_id);
        $data = new \stdClass();
        $data->id = $node->id;
        $data->title = Node::GetTitle($node, $node_id);
        $data->json = $node->data->json;
        //$data->enabled = true;
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);



	}


    public static function Data(){
        return  [
            'name'=>'Action',
            'title'=>\Yii::t('app/editor', 'Action'),
            'type'=>\Yii::t('app/editor', 'Effect'),
            'controls'=>[
                [
                    'type'=>'string',
                    'name' => 'title',
                    'title'=>\Yii::t('app/editor', 'Title'),
                    'default'=>'Title',
                ],

                [
                    'type'=>'string',
                    'name' => 'json',
                    'title'=>\Yii::t('app/editor', 'JSON'),
                    'default'=>'{}',
                ],

            ],
            'inputs'=>[],
            'output'=>[
                'name'=>'effect',
                'title'=>\Yii::t('app/editor', 'Effect'),
                'socket'=>'EffectSocket',

            ],
        ];

    }
}

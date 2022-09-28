<?php

namespace common\components\editor;


class NodeBoolProperty{

	public function setup($node, $inputs, $reader, $map){
      //  $this->type = "boolproperty";
        $data = new \stdClass();
        if(isset( $node->data->active) && $node->data->active){
            $data->active = true;
        }else{
            $data->active = false;
        } 
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
	}

    public static function Data(){
        return  [
            'name'=>'BoolProperty',
            'title'=>\Yii::t('app/editor', 'BoolProperty'),

            'type'=>[\Yii::t('app/editor', 'Effect')],
          // 'type'=>\Yii::t('app/editor', 'Effect'),
            'controls'=>[
                [
                    "type"=>"bool",
                    'name'=>'active',
                    'title'=>\Yii::t('app/editor', 'Active'),
                    'default'=> false,
                ],
                [
                    'type'=>'select',
                    'name'=>"type",
                    'title'=>\Yii::t('app/editor', 'Type'),
                    'options' => [
                        ["value" => "lockedScale", 'text'=> \Yii::t('app/editor', 'LockedScale')],
                    ],
                    'default'=>'lockedScale',
                ],
                
            

            ],
            'inputs'=>[
            ],
            'output'=>[
                'name'=>'effect',
                'title'=>\Yii::t('app/editor', 'Effect'),
                'socket'=>'EffectSocket',

            ],
        ];
    }
}

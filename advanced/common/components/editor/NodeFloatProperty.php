<?php

namespace common\components\editor;


class NodeFloatProperty{

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
            'name'=>'FloatProperty',
            'title'=>\Yii::t('app/editor', 'FloatProperty'),

            'type'=>[\Yii::t('app/editor', 'Effect')],
            //'type'=>\Yii::t('app/editor', 'Effect'),
            'controls'=>[
                
                [
                    "type"=>"number",
                    'name'=>'value',
                    'title'=>\Yii::t('app/editor', 'Value'),
                    'default'=> 0.8,
                ],
                [
                    'type'=>'select',
                    'name'=>"type",
                    'title'=>\Yii::t('app/editor', 'Type'),
                    'options' => [
                        ["value" => "transparent", 'text'=> \Yii::t('app/editor', 'Transparent')],
                    ],
                    'default'=>'transparent',
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

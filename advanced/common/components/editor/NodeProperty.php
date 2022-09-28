<?php

namespace common\components\editor;


class NodeProperty{

	public function setup($node, $inputs, $reader, $map){
        $this->type = "property";
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
            'name'=>'Property',
            'title'=>\Yii::t('app/editor', 'Property'),
           // 'type'=>\Yii::t('app/editor', 'Effect'),
            'type'=>[\Yii::t('app/editor', 'Effect')],
            'controls'=>[
                [
                    "type"=>"bool",
                    'name'=>'active',
                    'title'=>\Yii::t('app/editor', 'Active'),
                    'default'=> false,
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

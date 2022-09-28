<?php

namespace common\components\editor;

use stdClass;

class NodeOutput{
	public function setup($node, $inputs, $reader, $map){
	    if(isset($inputs['list'])){
            $this->list = $inputs['list'];
            return;
        }
		//$this->list = $inputs['list'];
		//$this->list = array();
		if(is_array($inputs['samples'])  && !empty($inputs['samples'])){
           // $data = new \stdClass();
           // $data->component = "MrPP.SampleLib.SampleStorable";
           // $data->path = array("SampleRoot");
		    $sr = new \stdClass();
		    $sr->list = array();
            foreach($inputs['samples']  as $sample){
                array_push($sr->list, $sample->data);
            }
            $this->content = json_encode($sr,JSON_UNESCAPED_SLASHES);
            $this->version = new stdClass();
            $this->version->major = 1;
            $this->version->minor = 0;
            $this->version->patch = 0;
        }
        

    }

	public static function Data(){
	   return  [
            'name'=>'Output',
            'title'=>\Yii::t('app/editor', 'Root'),
            'controls'=>[],
            'inputs'=>[
                [
                    'name'=>'samples',
                    'title'=>\Yii::t('app/editor', 'Sample'),
                    'socket'=>'SampleSocket',
                    'multiple' => 'true',
                ],
         
            ],
            'output'=>null,
        ];
    }
}
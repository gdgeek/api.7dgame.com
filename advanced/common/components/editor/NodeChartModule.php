<?php

namespace common\components\editor;
use yii\helpers\Url;
class NodeChartModule extends Point{

	public function setup($node, $inputs, $reader, $map, $node_id){
        
        parent::setup($node, $inputs, $reader, $map, $node_id);
       
       




        $data = new \stdClass();
       
        
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);


   
	}



    public static function Data(){

        return  [
            'name'=>'ChartModule',
            'title'=>\Yii::t('app/editor', 'ChartModule'),

            'type'=>[\Yii::t('app/editor', 'Advance'), \Yii::t('app/editor', 'Point')],
            
            'controls'=>[
              
               
            ],
            'inputs'=>[
            
            ],
            'output'=>[
                'name'=>'point',
                'title'=>\Yii::t('app/editor', 'Point'),
                'socket'=>'PointSocket',
            ],
        ];
    }


}
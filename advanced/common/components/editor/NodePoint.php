<?php

namespace common\components\editor;


class NodePoint extends Point{



	public function setup($node, $inputs, $reader, $map, $node_id){
    
        parent::setup($node, $inputs, $reader, $map, $node_id);
       
        $this->type = "point";
        

        $this->points = array();

        if (isset($inputs['point'])) {
        
            foreach($inputs['point'] as $point){
                $point->parent = $this->id;
                array_push($this->points, $point);
            }
        }
        
      

	}


    public static function Data(){
        return  [
            'name'=>'Point',
            'title'=>\Yii::t('app/editor', 'Point'),

            'type'=>[\Yii::t('app/editor', 'Point')],
          //  'type' => 'Point',
            'controls'=>[
                 [
                    'type' => 'string',
                    'name' => "title",
                    'title' => \Yii::t('app/editor', 'Title'),
                    'default' => 'Title',
                ],

            ],  'inputs' => [
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
                [
                    'name' => 'point',
                    'title' => \Yii::t('app/editor', 'Point'),
                    'socket' => 'PointSocket',
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

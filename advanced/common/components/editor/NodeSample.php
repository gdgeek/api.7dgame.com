<?php

namespace common\components\editor;

class NodeSample
{
    private function listPoints($points){
    
        $ret = array();
        foreach($points as $point){
            array_push($ret, $point);
            if(isset($point->points)){
                $next = $this->listPoints($point->points);
                $ret = array_merge($ret, $next);
                unset($point->points);
            }
        }
        return $ret;
    }
    public function setup($node, $inputs, $reader, $map, $node_id)
    {
        $this->id = $node->id;
        $this->title = 'Title';
        
        if (isset($inputs["transform"][0])) {
            $this->transform = $inputs["transform"][0];
        }

        if (isset($node->data->title)) {
            $this->title = $node_id.'.' .$node->id . ':' . $node->data->title;
        }

        $this->points = array();

        if (isset($inputs['point'])) {
        
            foreach($inputs['point'] as $point){
                $point->parent = $this->id;
                array_push($this->points, $point);
            }
        }
    
        $this->points = $this->listPoints($this->points);
        $this->addons = $inputs['addon'];
     
        if (isset($inputs['toolbar'][0])) {
            $this->toolbar = $inputs['toolbar'][0];
        }
    
    }

    public static function Data()
    {

        return [
            'name' => 'Sample',
            'title' => \Yii::t('app/editor', 'Sample'),
            'controls' => [
                [
                    'type' => 'string',
                    'name' => "title",
                    'title' => \Yii::t('app/editor', 'Title'),
                    'default' => 'Title',
                ],
            ],

            'inputs' => [

                [
                    'name' => 'transform',
                    'title' => \Yii::t('app/editor', 'Local'),
                    'socket' => 'TransformSocket',
                    'multiple' => false,
                ],


                [
                    'name' => 'point',
                    'title' => \Yii::t('app/editor', 'Point'),
                    'socket' => 'PointSocket',
                    'multiple' => true,
                ],
               
                [
                    'name' => 'addon',
                    'title' => \Yii::t('app/editor', 'Addon'),
                    'socket' => 'AddonSocket',
                    'multiple' => true,
                ],
               
            ],
            'output' => [
                'name' => 'sample',
                'title' => \Yii::t('app/editor', 'Sample'),
                'socket' => 'SampleSocket',
            ],
        ];
    }
}

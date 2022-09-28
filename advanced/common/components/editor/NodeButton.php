<?php

namespace common\components\editor;



class NodeButton extends Node{

	
 
	protected function sort($x,$y){
        if ($x->sort == $y->sort) return 0;
        return ($x->sort > $y->sort) ? 1 : -1;

	}

	public function setup($node, $inputs, $reader, $map, $node_id, &$category){
		$this->id = $node->id;


		$this->type = "button";
	    $this->head = $node->data->head;
		if(isset($node->data->icon)){
			$this->key = $node->data->icon;
		}else{
			$this->key = 'cube';
		}

		$this->action = new Action($node->id, $node_id.".".$node->id.":".$node->data->action, "{}", true);


        $this->sort = $node->data->sort;

        
        if(isset($inputs['button'])){
			$buttons = $inputs['button'];
			usort($buttons, array('common\\components\\editor\\NodeButton','sort'));
            $this->buttons = array();
			foreach($buttons as $b){
				unset($b->sort);    
                $b->parent = $this->id;
                array_push($this->buttons, $b);
			}
		}

		if(isset($category['Action'])){
			array_push($category['Action'], $this->action);
		}
	}

    public static function Data(){
        return  [
            'name'=>'Button',
            'title'=>\Yii::t('app/editor', 'Button'),
            'type'=>[\Yii::t('app/editor', 'Other')],
            'controls'=>[
             
                [
                    'type'=>'string',
                    'name' => 'head',
                    'title' => \Yii::t('app/editor', 'Head'),
                    'default'=>'Head',
                ],
                [
                    'type'=>'string',
                    'name' => 'action',
                    'title' => \Yii::t('app/editor', 'Action'),
                    'default'=>'Action',
                ],
                [
                    'type'=>'number',
                    'name' => 'sort',
                    'title' => \Yii::t('app/editor', 'Sort'),
                    'default'=>0,
                ],


                [
                    'type'=>'select',
                    'name'=>"icon",
                    'title'=>\Yii::t('app/editor', 'Icon'),
                    'options' => [
                        ["value" => "calendar", 'text'=> '<i class="fas fa-calendar "></i>calendar'],
                        ["value" => "bell", 'text'=> '<i class="fas fa-bell fa-fw"></i>bell'],
                        ["value" => "check", 'text'=> '<i class="fas fa-check fa-fw"></i>check'],

                        ["value" => "compress", 'text'=> '<i class="fas fa-compress"></i>compress'],
                        ["value" => "expand", 'text'=> '<i class="fas fa-expand fa-fw"></i>expand'],
                        ["value" => "cube", 'text'=> '<i class="fas fa-cube fa-fw"></i>cube'],
                        ["value" => "arrow-down", 'text'=> '<i class="fas fa-arrow-down"></i>arrow-down'],
                        ["value" => "arrow-left", 'text'=> '<i class="fas fa-arrow-left"></i>arrow-left'],
                        ["value" => "arrow-right", 'text'=> '<i class="fas fa-arrow-right"></i>arrow-right'],
                        ["value" => "arrow-up", 'text'=> '<i class="fas fa-arrow-up"></i>arrow-up'],


                        ["value" => "anchor", 'text'=> '<i class="fas fa-anchor"></i>anchor'],
                        ["value" => "info", 'text'=> '<i class="fas fa-info"></i>info'],
                        ["value" => "edit", 'text'=> '<i class="fas fa-edit"></i>edit'],
                        ["value" => "cloud", 'text'=> '<i class="fas fa-cloud"></i>cloud'],
                        ["value" => "heart", 'text'=> '<i class="fas fa-heart"></i>heart'],
                        ["value" => "eye", 'text'=> '<i class="fas fa-eye"></i>eye'],


                    ],
                    'default'=>'calendar',
                ],
            ],
            'inputs'=>[
                [
                    'name'=>'button',
                    'title'=>\Yii::t('app/editor', 'Button'),
                    'socket'=>'ButtonSocket',
                    'multiple' => true,
                ],
            ],
            'output'=>[
                'name'=>'button',
                'title'=>\Yii::t('app/editor', 'Button'),
                'socket'=>'ButtonSocket',

            ],
        ];

    }
}
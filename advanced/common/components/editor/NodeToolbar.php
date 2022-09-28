<?php

namespace common\components\editor;


class NodeToolbar{




 
	protected function sort($x,$y){

	
        if ($x->sort == $y->sort) return 0;
        return ($x->sort > $y->sort) ? 1 : -1;

	}

	

	public function array3($v3) {


		$vector3 = array();
		array_push($vector3, floatval($v3[0]));
		array_push($vector3, floatval($v3[1]));
		array_push($vector3, floatval($v3[2]));
		return $vector3;
	}
	 private function listButtons($buttons){
    
        $ret = array();
        foreach($buttons as $button){
            array_push($ret, $button);
            if(isset($button->buttons)){
                $next = $this->listButtons($button->buttons);
                $ret = array_merge($ret, $next);
                unset($button->buttons);
            }
        }
        return $ret;
    }

	public function setup($node, $inputs, $reader, $map, $node_id){
        $this->type = "toolbar";
        $data = new \stdClass();

        $data->destroy = $node->data->destroy;
		    
		
		if(isset($inputs['button'])){
		
			$buttons = $inputs['button'];
			usort($buttons, array('common\\components\\editor\\NodeToolbar','sort'));
			    
			foreach($buttons as $b){
				unset($b->sort);
                $b->parent = -1;
			}
			//$data->buttons = $buttons; 
            
            $data->buttons = $this->listButtons($buttons);
		}


        
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
     
	}





    public static function Data(){
        return  [
            'name'=>'Toolbar',
            'title'=>\Yii::t('app/editor', 'Toolbar'),
           // 'type'=>\Yii::t('app/editor', 'Addon'),

            'type'=>[\Yii::t('app/editor', 'Addon')],
            'controls'=>[
                [
                    'type'=>'bool',
                    'name' => 'destroy',
                    'title' => \Yii::t('app/editor', 'Destroy'),
                    'default' => 'false',
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
                'name'=>'addon',
                'title'=>\Yii::t('app/addon', 'Addon'),
                'socket'=>'AddonSocket',	

            ],
        ];

    }
}
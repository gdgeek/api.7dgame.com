<?php

namespace common\components;
use Yii;
use yii\helpers\Url;



/**
 * template module definition class
 */
class ComponentData
{
    
    public $component;
    public $path;
    public $serialize;
    private $list;
    
    public function __construct($component, $path)
    {
        $this->component = $component;
        $this->path = $path;

        
        $this->list = array();
        $this->serialize = new \stdClass();
        $this->serialize->list = array();
    }
    public function addEntity($entity){
        array_push($this->list, $entity);

    }
    
 
    public function complete(){
        $serialize = new \stdClass();
        $serialize->list = array();
        foreach($this->list as $val){
            array_push($serialize->list, json_encode($val,JSON_UNESCAPED_SLASHES));
        }
        $this->serialize = json_encode($serialize, JSON_UNESCAPED_SLASHES);

    }

}

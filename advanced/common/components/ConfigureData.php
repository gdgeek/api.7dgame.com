<?php

namespace common\components;
use Yii;
use yii\helpers\Url;



/**
 * template module definition class
 */
class ConfigureData
{
    
    public $list;
    public function __construct()
    {
       $this->list = array();
    }
    public function addComponent($component){
        array_push($this->list, $component);
    }


}

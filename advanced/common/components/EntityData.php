<?php

namespace common\components;
use Yii;
use yii\helpers\Url;



/**
 * template module definition class
 */
class EntityData
{
    public $id;
    public $transform;  
    public $polygenList;
    public $toolbar;
    private $toolbarTemplate;
    
    public function __construct($id, $transform)
    {
        $this->id = $id;
        $this->transform = $transform;
        $this->polygenList = array();
    }
    public function setToolbar($template){
        $this->toolbar = new \stdClass();
        $this->toolbar->desotry = false;
        $this->toolbar->template = $template;
        $this->toolbar->enabled = true;

    }
 
    public function addPolygen($polygen){
        array_push($this->polygenList, $polygen);
    }

   

}

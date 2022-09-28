<?php

namespace common\components;
use Yii;
use yii\helpers\Url;



/**
 * template module definition class
 */
class ProjectData
{
    
    public $logic;
    public $configure;
    public function __construct($logic, $configure)
    {
       $this->logic = $logic;
       $this->configure = $configure;
    }
   


}

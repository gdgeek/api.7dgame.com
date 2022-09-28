<?php

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class TestWidget extends Widget
{
  
    public function init()
    {
        parent::init();

    }

    public function run()
    {     
   
        return "1";
    }
}
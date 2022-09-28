<?php

namespace common\components;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Url;



/**
 * template module definition class
 */
class Vector2Data
{
    
    public $x;
    public $y;

    public function __construct()
    { 
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    public function __construct3($x, $y)
    {
        $this->x = floatval($x);
        $this->y = floatval($y);
    }
    public function __construct1($array)
    {
        $this->x = floatval($array[0]);
        $this->y = floatval($array[1]);
    }

    
    public static function From($vector2){
    
        return new Vector2Data($vector2->x, $vector2->y);
    }
}

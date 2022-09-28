<?php

namespace common\components;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Url;



/**
 * template module definition class
 */
class Vector3Data
{
    
    public $x;
    public $y;
    public $z;
    
    public function __construct()
    { 
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    public function __construct3($x, $y, $z)
    {
        $this->x = floatval($x);
        $this->y = floatval($y);
        $this->z = floatval($z);
    }
    public function __construct1($array)
    {
        $this->x = floatval($array[0]);
        $this->y = floatval($array[1]);
        $this->z = floatval($array[2]);
    }

    
    public static function From($vector3){
    
        return new Vector3Data($vector3->x,$vector3->y,$vector3->z);
    }
}

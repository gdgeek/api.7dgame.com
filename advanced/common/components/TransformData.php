<?php

namespace common\components;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Url;

use common\components\Vector3Data;


/**
 * template module definition class
 */
class TransformData
{
    
    public $position;
    public $angle;
    public $scale;
    public function __construct($position, $angle, $scale)
    {
        $this->position = $position;
        $this->angle = $angle;
        $this->scale = $scale;
    }

    public static function From($transform){
        return new TransformData(
            Vector3Data::From($transform->position),
            Vector3Data::From($transform->angle),
            Vector3Data::From($transform->scale)
        );
    }



}

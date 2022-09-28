<?php

namespace common\components;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Url;

use api\modules\v1\models\File;


/**
 * template module definition class
 */
class MeshData
{
    
    public $file;
    public $type;
    public function __construct($file, $type = "fbx")
    {
        $this->file = $file;
        $this->type = $type;
    }


}

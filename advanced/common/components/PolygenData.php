<?php

namespace common\components;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Url;

use api\modules\v1\models\Resource;
use common\components\FileData;
use common\components\TransformData;
use common\components\Vector3Data;


/**
 * template module definition class
 */
class PolygenData// extends BaseObject
{
    
    public $id;
    public $mesh;
    public $transform;

    public function __construct($id, $polygen, $transform)
    {


        $this->id = $id;
        $this->mesh = new MeshData(new FileData( $polygen->file, true,'zip'));
		$this->transform = $transform;

    }



}

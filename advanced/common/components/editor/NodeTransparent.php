<?php
/**
 * Created by PhpStorm.
 * User: cngua
 * Date: 2019/11/1
 * Time: 14:52
 */

namespace common\components\editor;

use yii\db\Query;
use common\components\Vector2Data;

class NodeTransparent
{
    
    public function setup($node, $inputs, $reader, $map, $node_id)
    {
        
        $this->type = "transparent";

      
    
        $data = new \stdClass();
        $data->alpha =  $node->data->alpha;
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

     
    }

    public static function Data()
    {

        return [
            'name'=>'Transparent',
            'title'=>\Yii::t('app/editor', 'Transparent'),
           // 'type'=>\Yii::t('app/editor', 'Effect'),

            'type'=>[\Yii::t('app/editor', 'Effect'), \Yii::t('app/editor', 'Float')],
            'controls'=>[

                [
                    "type"=>"number",
                    'name'=>'alpha',
                    'title'=>\Yii::t('app/editor', 'Alpha'),
                    'default'=> 0.8,
                ],
              

            ],
            'inputs'=>[
            ],
            'output'=>[
                'name'=>'effect',
                'title'=>\Yii::t('app/editor', 'Effect'),
                'socket'=>'EffectSocket',

            ],
        ];
      
    }
}
<?php

namespace common\components\editor;

class NodeOptions
{
   
    public function setup($node, $inputs, $reader, $map, $node_id)
    {
        $this->id = $node->id;
        $this->type = "options";
        $data = new \stdClass();
        $data->lockedScale = $node->data->LockedScale;
      
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
      
    }

    public static function Data()
    {

        return [
            'name' => 'Options',
            'title' => \Yii::t('app/editor', 'Options'),
            //'type'=>\Yii::t('app/editor', 'Effect'),

            'type'=>[\Yii::t('app/editor', 'Effect')],
            'controls' => [

                [
                    'type' => 'bool',
                    'name' => "LockedScale",
                    'title' => \Yii::t('app/editor', 'LockedScale'),
                    'default' => false,
                ],
            ],

            'inputs' => [
            ],
            'output' => [
                'name' => 'effect',
                'title' => \Yii::t('app/editor', 'Effect'),
                'socket' => 'EffectSocket',
            ],
        ];
    }
}

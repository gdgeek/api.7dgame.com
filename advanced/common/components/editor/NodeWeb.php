<?php

namespace common\components\editor;

use yii\db\Query;

use common\components\Vector2Data;

class NodeWeb extends Point
{
	

    public function setup($node, $inputs, $reader, $map, $node_id)
    {
        parent::setup($node, $inputs, $reader, $map, $node_id);
      
        $this->type = "web";
        $data = new \stdClass();
        $data->size = new Vector2Data($node->data->size);
		$data->url = $node->data->url;
        $data->playOnAwake = (bool)$node->data->playOnAwake;
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }

    public static function Data()
    {
        return [
            'name' => 'Web',
            'title' => \Yii::t('app/editor', 'Web'),
           // 'type' => 'Point',

            'type'=>[\Yii::t('app/editor', 'Point')],
            'controls' => [
                [
                    'type' => 'string',
                    'name' => 'title',
                    'title' => \Yii::t('app/editor', 'Title'),
                    'default' => 'Title',
                ],
				 [
                    'type' => 'string',
                    'name' => 'url',
                    'title' => \Yii::t('app/editor', 'Url'),
                    'default' => 'http://',
                ],
                  
               
                [
                    'type' => 'vector2',
                    'name' => 'size',
                    'title' => \Yii::t('app/editor', 'Size(m)'),
                    'default' => [0.8,0.6],
                ],
                [
                    'type' => 'bool',
                    'name' => 'playOnAwake',
                    'title' => \Yii::t('app/editor', 'Play On Awake'),
                    'default' => true,
                ]
            ],
            'inputs' => [
                [
                    'name' => 'transform',
                    'title' => \Yii::t('app/editor', 'Local'),
                    'socket' => 'TransformSocket',
                    'multiple' => false,
                ],
                [
                    'name'=>'effects',
                    'title'=>\Yii::t('app/editor', 'Effect'),
                    'socket'=>'EffectSocket',
                    'multiple' => true,
                ],
            ],
            'output'=>[
                'name'=>'point',
                'title'=>\Yii::t('app/editor', 'Point'),
                'socket'=>'PointSocket',
            ],
        ];
    }
}

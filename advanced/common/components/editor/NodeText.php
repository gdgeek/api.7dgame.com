<?php
/*
 * @Author: guanfei 
 * @Date: 2020-03-26 11:26:03 
 * @Last Modified by: guanfei
 * @Last Modified time: 2020-04-22 16:09:50
 */

namespace common\components\editor;

use stdClass;
use common\components\Vector2Data;
use yii\db\Query;

class NodeText extends Point
{
    public function setup($node, $inputs, $reader, $map, $node_id)
    {
        parent::setup($node, $inputs, $reader, $map, $node_id);
        $data = new stdClass();
        $this->type = "text";
        $data->color = $node->data->color;
        if (isset($node->data->content)) {
            $data->content = $node->data->content;
        }
        if (isset($inputs["action"][0])) {
            $this->action = $inputs["action"][0];
        }
        if (isset($inputs["properties"])) {
            $data->properties = $inputs["properties"];
        }

        $data->size = $node->data->size;
        $this->data = json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }

    public static function Data()
    {
        return [
            'name' => 'Text',
            'title' => \Yii::t('app/editor', 'Text'),
           // 'type'=>\Yii::t('app/editor', 'Point'),

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
                    'name' => 'content',
                    'title' => \Yii::t('app/editor', 'Content'),
                    'default' => \Yii::t('app/editor', 'Enter the text you want to display.'),
                ],
                [
                    'type' => 'number',
                    'name' => 'size',
                    'title' => \Yii::t('app/editor', 'Size'),
                    'default' => '0.1',
                ],
          
                [
                    'type' => 'color-picker',
                    'name' => 'color',
                    'title' => \Yii::t('app/editor', 'Color'),
                    'default' => '0.1',
                ],
          

            ],
            'inputs' => [
                [
                    'name' => 'transform',
                    'title' => \Yii::t('app/editor', 'Local'),
                    'socket' => 'TransformSocket',
                    'multiple' => false,
                ],

               
                [
                    'name' => 'effects',
                    'title' => \Yii::t('app/editor', 'Effect'),
                    'socket' => 'EffectSocket',
                    'multiple' => true,
                ],
            ],
            'output' => [
                'name' => 'point',
                'title' => \Yii::t('app/editor', 'Point'),
                'socket' => 'PointSocket',
            ]
        ];
    }
}

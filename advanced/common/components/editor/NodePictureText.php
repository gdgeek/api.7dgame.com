<?php
/*
 * @Author: guanfei 
 * @Date: 2020-05-21 17:33:41 
 * @Last Modified by: guanfei
 * @Last Modified time: 2020-05-21 17:55:51
 */

namespace common\components\editor;

use yii\db\Query;

use api\modules\v1\models\Resource;

//use common\models\Picture;
use common\components\Vector2Data;

class NodePictureText extends Point
{
    public function setup($node, $inputs, $reader, $map, $node_id)
    {
        parent::setup($node, $inputs, $reader, $map, $node_id);
        $this->type = "pictureText";
        $picture = Resource::findOne(['id'=>$node->data->picture, 'type'=>'picture']);
        if (!isset($picture)) {
            echo "error for picture!";
            return;
        }
        $file = $picture->file;

        $data = new \stdClass();
        $data->size = new Vector2Data($node->data->width, $node->data->height);

        $data->file = new \stdClass();
        $data->file->url = $file->url;
        $data->file->md5 = $file->md5;
        $data->file->type = $file->type;
        if (isset($node->data->content)) {
            $data->content = $node->data->content;
        }
        $this->data = json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
    }

    public static function Data()
    {
        return [
            'name' => 'PictureText',
            'title' => \Yii::t('app/editor', 'PictureText'),

            'type'=>[\Yii::t('app/editor', 'Point')],
           // 'type' => ['Point'],
            'controls' => [
                [
                    'type' => 'string',
                    'name' => 'title',
                    'title' => \Yii::t('app/editor', 'Title'),
                    'default' => 'Title',
                ],
                [
                    'type' => 'number',
                    'name' => 'width',
                    'title' => \Yii::t('app/editor', 'Width'),
                    'default' => '1920',
                ],
                [
                    'type' => 'number',
                    'name' => 'height',
                    'title' => \Yii::t('app/editor', 'Height'),
                    'default' => '1920',
                ],
                [
                    'type' => 'resource',
                    'name' => 'picture',
                    'title' => \Yii::t('app/editor', 'Picture'),
                    'func' => 'setPictures',
                    'default' => 'None',
                ],
                [
                    'type' => 'string',
                    'name' => 'content',
                    'title' => \Yii::t('app/editor', 'Content'),
                    'default' => \Yii::t('app/editor', 'Enter the text you want to display.'),
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

            ],
        ];
    }
}

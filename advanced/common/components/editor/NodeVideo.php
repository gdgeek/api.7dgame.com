<?php

namespace common\components\editor;

use yii\db\Query;

use api\modules\v1\models\Resource;

use common\components\Vector2Data;

class NodeVideo extends Point
{


    public function setup($node, $inputs, $reader, $map, $node_id)
    {
        parent::setup($node, $inputs, $reader, $map, $node_id);
      
        $this->type = "video";
      
        $video = Resource::findOne(['id' => $node->data->video, 'type'=>'video']);
        if(isset($video)){
            $file = $video->file;
            $data = new \stdClass();
            $data->width = $node->data->width;
            $data->file = new \stdClass();
            $data->file->url = $file->url;
            $data->file->md5 = $file->md5;
            $data->file->type = $file->type;
            $data->playOnAwake = $node->data->playOnAwake;
            $data->loop = $node->data->loop;
            $data->console = $node->data->console;
            $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
        }else{
            $this->data = json_encode(null, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
        }
        
     

     
    }

    public static function Data()
    {
        return [
            'name' => 'Video',
            'title' => \Yii::t('app/editor', 'Video'),
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
                    'type' => 'number',
                    'name' => 'width',
                    'title' => \Yii::t('app/editor', 'Width'),
                    'default' => '0.5',
                ],
               
                [
                    'type' => 'bool',
                    'name' => 'playOnAwake',
                    'title' => \Yii::t('app/editor', 'Play On Awake'),
                    'default' => true,
                ],
               
                [
                    'type' => 'bool',
                    'name' => 'loop',
                    'title' => \Yii::t('app/editor', 'Loop'),
                    'default' => true,
                ],
               
                [
                    'type' => 'bool',
                    'name' => 'console',
                    'title' => \Yii::t('app/editor', 'Console'),
                    'default' => true,
                ],
                [
                    'type' => 'resource',
                    'name' => 'video',
                    'title' => \Yii::t('app/editor', 'Video'),
                    'func' => 'setVideos',
                    'default' => 'None',
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

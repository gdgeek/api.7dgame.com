<?php

namespace common\components\editor;

use yii\db\Query;

use common\models\Sound;

class NodeSound extends Point
{


    public function setup($node, $inputs, $reader, $map, $node_id)
    {
        parent::setup($node, $inputs, $reader, $map, $node_id);
      
        $this->type = "sound";
        $sound = Sound::findOne($node->data->sound);
        if(!isset($sound)){
            echo "error for $sound";
            return;
        }
        
        $file = $sound->file;
        $data = new \stdClass();
        $data->file = new \stdClass();
        $data->file->url = $file->url;
        $data->file->md5 = $file->md5;
        $data->file->type = $file->type;
        $this->data = json_encode($data,JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);

     
    }

    public static function Data()
    {
        return [
            'name' => 'Sound',
            'title' => \Yii::t('app/editor', 'Sound'),
          //  'type' => 'Point',

            'type'=>[\Yii::t('app/editor', 'Point')],
            'controls' => [
                [
                    'type' => 'string',
                    'name' => 'title',
                    'title' => \Yii::t('app/editor', 'Title'),
                    'default' => 'Title',
                ],
                [
                    'type' => 'resource',
                    'name' => 'video',
                    'title' => \Yii::t('app/editor', 'Sound'),
                    'func' => 'setSound',
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

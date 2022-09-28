<?php

namespace common\components\editor;

use yii\helpers\Url;
class NodeSampleEntity
{
    public function setup($node, $inputs, $reader, $map, $node_id, &$category)
    {
        $this->data = null;
        if (isset($map[$node->id])) {
            $this->data = $reader->readData($map[$node->id], $map, 'sample', $node->id, $category);
        }
    }

    public static function Data()
    {
        return [
            'name' => 'SampleEntity',
            'title' => \Yii::t('app/editor', 'SampleEntity'),
            'controls' => [
                [
                    'type' => 'edit',
                    'name' => 'editor',
                    'template' => 'sample',
                ],
                
            ],
            'inputs' => [],
            'output' => [
                'name' => 'sample',
                'title' => \Yii::t('app/editor', 'Sample'),
                'socket' => 'SampleSocket',
            ],
        ];
    }
}
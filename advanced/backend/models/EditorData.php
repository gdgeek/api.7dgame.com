<?php

namespace backend\models;

use Yii;
use api\modules\v1\models\User;

/**
 * This is the model class for table "editor_data".
 *
 * @property int $id
 * @property int $project_id
 * @property int $user_id
 * @property int $node_id
 * @property string $type
 * @property string $data
 * @property string $serialization
 */
class EditorData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'editor_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'user_id', 'node_id'], 'integer'],
            [['data', 'serialization'], 'string'],
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'user_id' => 'User ID',
            'node_id' => 'Node ID',
            'type' => 'Type',
            'data' => 'Data',
            'serialization' => 'Serialization',
        ];
    }

    /**
     * {@inheritdoc}
     * @return EditorDataQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EditorDataQuery(get_called_class());
    }
}

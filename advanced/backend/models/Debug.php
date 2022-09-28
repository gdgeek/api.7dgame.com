<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "debug".
 *
 * @property int $id
 * @property string $title
 * @property string $body
 * @property int $submitter_id
 * @property int $solver_id
 * @property string $reply
 */
class Debug extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'debug';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['body', 'reply'], 'string'],
            [['submitter_id', 'solver_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'body' => 'Body',
            'submitter_id' => 'Submitter ID',
            'solver_id' => 'Solver ID',
            'reply' => 'Reply',
        ];
    }

    /**
     * {@inheritdoc}
     * @return DebugQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DebugQuery(get_called_class());
    }
}

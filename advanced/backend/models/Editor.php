<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "editor".
 *
 * @property int $id
 * @property int $project
 * @property string $template
 * @property string $data
 */
class Editor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'editor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project', 'template'], 'required'],
            [['project'], 'integer'],
            [['template', 'data'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project' => 'Project',
            'template' => 'Template',
            'data' => 'Data',
        ];
    }

    /**
     * {@inheritdoc}
     * @return EditorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EditorQuery(get_called_class());
    }
}

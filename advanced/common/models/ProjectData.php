<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "project_data".
 *
 * @property int $id
 * @property string|null $configuration
 * @property string|null $logic
 *
 * @property ProjectIndex[] $projectIndices
 */
class ProjectData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['configuration', 'logic'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'configuration' => 'Configuration',
            'logic' => 'Logic',
        ];
    }

    /**
     * Gets query for [[ProjectIndices]].
     *
     * @return \yii\db\ActiveQuery|ProjectIndexQuery
     */
    public function getProjectIndices()
    {
        return $this->hasMany(ProjectIndex::className(), ['data_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ProjectDataQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProjectDataQuery(get_called_class());
    }
}

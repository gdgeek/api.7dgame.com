<?php

namespace common\models;

use api\modules\v1\models\User;
use Yii;

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
 *
 * @property Project $project
 * @property User $user
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
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'project_id' => Yii::t('app', 'Project ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'node_id' => Yii::t('app', 'Node ID'),
            'type' => Yii::t('app', 'Type'),
            'data' => Yii::t('app', 'Data'),
            'serialization' => Yii::t('app', 'Serialization'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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

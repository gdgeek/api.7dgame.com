<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "logic".
 *
 * @property int $id
 * @property int $project_id
 * @property int|null $node_id
 * @property int $user_id
 * @property string|null $dom
 * @property string|null $code
 *
 * @property Project $project
 * @property User $user
 */
class Logic extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'user_id'], 'required'],
            [['project_id', 'node_id', 'user_id'], 'integer'],
            [['dom', 'code'], 'string'],
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
            'node_id' => Yii::t('app', 'Node ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'dom' => Yii::t('app', 'Dom'),
            'code' => Yii::t('app', 'Code'),
        ];
    }

    /**
     * Gets query for [[Project]].
     *
     * @return \yii\db\ActiveQuery|ProjectQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return LogicQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LogicQuery(get_called_class());
    }
}

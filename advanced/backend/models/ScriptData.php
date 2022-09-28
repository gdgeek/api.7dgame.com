<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "script_data".
 *
 * @property int $id
 * @property int $project_id
 * @property int $user_id
 * @property string $dom
 * @property string $code
 */
class ScriptData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'script_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'user_id'], 'required'],
            [['project_id', 'user_id'], 'integer'],
            [['dom', 'code'], 'string'],
            [['project_id'], 'unique'],
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
            'dom' => Yii::t('app', 'Dom'),
            'code' => Yii::t('app', 'Code'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return ScriptDataQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ScriptDataQuery(get_called_class());
    }
}

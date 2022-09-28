<?php

namespace backend\models;

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
     * {@inheritdoc}
     * @return LogicQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LogicQuery(get_called_class());
    }
}

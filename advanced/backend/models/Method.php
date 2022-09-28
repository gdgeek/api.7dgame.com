<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "method".
 *
 * @property int $id
 * @property int $user_id
 * @property string $definition
 * @property string $generator
 */
class Method extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'method';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['definition', 'generator'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'definition' => Yii::t('app', '脚本定义'),
            'generator' => Yii::t('app', '程序产生器'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return MethodQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MethodQuery(get_called_class());
    }
}

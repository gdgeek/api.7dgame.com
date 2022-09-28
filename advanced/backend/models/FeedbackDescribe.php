<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "feedback_describe".
 *
 * @property int $id
 * @property string $describe
 * @property int $order
 */
class FeedbackDescribe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feedback_describe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order'], 'integer'],
            [['describe'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'describe' => 'Describe',
            'order' => 'Order',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FeedbackDescribeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FeedbackDescribeQuery(get_called_class());
    }
}

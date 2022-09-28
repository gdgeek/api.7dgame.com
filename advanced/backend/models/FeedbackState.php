<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "feedback_state".
 *
 * @property int $id
 * @property string $state
 * @property int $order
 */
class FeedbackState extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feedback_state';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order'], 'integer'],
            [['state'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'state' => 'State',
            'order' => 'Order',
        ];
    }

    /**
     * {@inheritdoc}
     * @return FeedbackStateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new FeedbackStateQuery(get_called_class());
    }
}

<?php

namespace api\modules\v1\models;

use Yii;


use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "recharge".
 *
 * @property int $id
 * @property string $uuid
 * @property int $duration
 * @property int|null $activation_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Activation $activation
 */
class Recharge extends \yii\db\ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                    
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recharge';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'duration'], 'required'],
            [['duration', 'activation_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['uuid'], 'string', 'max' => 255],
            [['activation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Activation::className(), 'targetAttribute' => ['activation_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uuid' => Yii::t('app', 'Uuid'),
            'duration' => Yii::t('app', 'Duration'),
            'activation_id' => Yii::t('app', 'Activation ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Activation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivation()
    {
        return $this->hasOne(Activation::className(), ['id' => 'activation_id']);
    }
}

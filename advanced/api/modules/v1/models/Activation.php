<?php

namespace api\modules\v1\models;

use Yii;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "activation".
 *
 * @property int $id
 * @property string $begin
 * @property int $duration
 * @property int $device_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Device $device
 * @property Recharge[] $recharges
 */
class Activation extends \yii\db\ActiveRecord
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
        return 'activation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['begin', 'duration', 'device_id',], 'required'],
            [['begin', 'created_at', 'updated_at'], 'safe'],
            [['duration', 'device_id'], 'integer'],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::className(), 'targetAttribute' => ['device_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'begin' => Yii::t('app', 'Begin'),
            'duration' => Yii::t('app', 'Duration'),
            'device_id' => Yii::t('app', 'Device ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Device]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id']);
    }

    /**
     * Gets query for [[Recharges]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecharges()
    {
        return $this->hasMany(Recharge::className(), ['activation_id' => 'id']);
    }
}

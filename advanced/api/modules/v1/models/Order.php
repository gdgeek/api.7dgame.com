<?php

namespace api\modules\v1\models;

use Yii;

use api\modules\v1\models\User;

use yii\db\Expression;

use yii\behaviors\TimestampBehavior; 
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property string $uuid
 * @property string $prepay_id
 * @property int $trade_id
 * @property string $created_at
 * @property int|null $state
 * @property string|null $payed_time
 *
 * @property Trade $trade
 */
class Order extends \yii\db\ActiveRecord
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
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => false,
            ],
       ]; 
   } 
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

     /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'trade_id'], 'required'],
            [['user_id', 'trade_id', 'state'], 'integer'],
            [['created_at', 'updated_at','payed_time'], 'safe'],
            [['uuid', 'prepay_id'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['prepay_id'], 'unique'],
            [['trade_id', 'user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Trade::className(), 'targetAttribute' => ['trade_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uuid' => 'Uuid',
            'prepay_id' => 'Prepay ID',
            'trade_id' => 'Trade ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'state' => 'State',
            'payed_time' => 'Payed Time',
            'user_id' => 'User Id',
            
        ];
    }

    /**
     * Gets query for [[Trade]].
     *
     * @return \yii\db\ActiveQuery|TradeQuery
     */
    public function getTrade()
    {
        return $this->hasOne(Trade::className(), ['id' => 'trade_id']);
    }

    /**
     * {@inheritdoc}
     * @return OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderQuery(get_called_class());
    }
}

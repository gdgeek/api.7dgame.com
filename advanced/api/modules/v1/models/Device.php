<?php

namespace api\modules\v1\models;

use Yii;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "device".
 *
 * @property int $id
 * @property string $type
 * @property string|null $uuid
 * @property int|null $owner_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Activation[] $activations
 * @property User $owner
 */
class Device extends \yii\db\ActiveRecord
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
        return 'device';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'required'],
            [['owner_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['type', 'uuid'], 'string', 'max' => 255],
            [['uuid'], 'unique'],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['owner_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'uuid' => Yii::t('app', 'Uuid'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[Activations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivations()
    {
        return $this->hasMany(Activation::className(), ['device_id' => 'id']);
    }

    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::className(), ['id' => 'owner_id']);
    }
}

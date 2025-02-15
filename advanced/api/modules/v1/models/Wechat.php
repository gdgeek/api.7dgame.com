<?php

namespace api\modules\v1\models;

use Yii;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "wechat".
 *
 * @property int $id
 * @property string $openid
 * @property int $user_id
 * @property string|null $created_at
 * @property string|null $token
 *
 * @property User $user
 */
class Wechat extends \yii\db\ActiveRecord
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
            ]
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wechat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['openid'], 'required'],
            [['user_id'], 'integer'],
            [['created_at','updated_at'], 'safe'],
            [['openid', 'token'], 'string', 'max' => 255],
            [['openid'], 'unique'],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'openid' => 'Openid',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' =>'Updated At',
            'token' => 'Token',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}

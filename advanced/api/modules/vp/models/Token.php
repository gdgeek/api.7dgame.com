<?php

namespace api\modules\vp\models;

use api\modules\vp\models\User;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "vp_token".
 *
 * @property int $id
 * @property string $key
 * @property string $token
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $user_id
 * @property string|null $name
 *
 * @property VpLevel[] $vpLevels
 * @property User $user
 */

class Token extends \yii\db\ActiveRecord
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
        return 'vp_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'token'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id'], 'integer'],
            [['key', 'token', 'name'], 'string', 'max' => 255],
            [['key'], 'unique'],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'token' => 'Token',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'user_id' => 'User ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[VpLevels]].
     *
     * @return \yii\db\ActiveQuery|VpLevelQuery
     */
    public function getVpLevels()
    {
        return $this->hasMany(VpLevel::className(), ['player_id' => 'id']);
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
     * @return TokenQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TokenQuery(get_called_class());
    }
}

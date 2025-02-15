<?php

namespace app\modules\v1\models;

use Yii;

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
            [['created_at'], 'safe'],
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

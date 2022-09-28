<?php

namespace common\models;

use api\modules\v1\models\User;
use Yii;

/**
 * This is the model class for table "wx".
 *
 * @property int $id
 * @property string|null $wx_openid
 * @property string|null $token
 * @property int|null $user_id
 * @property string $created_at
 *
 * @property User $user
 */
class Wx extends \yii\db\ActiveRecord

{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wx';
    }
    public function setup($token, $openid)
    {
        $this->token = $token;
        $this->wx_openid = $openid;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['wx_openid', 'token'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'wx_openid' => 'Wx Openid',
            'token' => 'Token',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|UserQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return WxQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new WxQuery(get_called_class());
    }
}

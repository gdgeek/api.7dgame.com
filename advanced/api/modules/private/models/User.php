<?php

namespace api\modules\private\models;

use Yii;

class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'verification_token', 'access_token', 'wx_openid', 'nickname'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['email'], 'unique'],
            [['username'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',// 用户名
            'auth_key' => 'Auth Key', // 认证key
            'password_hash' => 'Password Hash',//密码
            'password_reset_token' => 'Password Reset Token', //充值密码token
            'email' => 'Email', //信箱
            'status' => 'Status', // 状态
            'created_at' => 'Created At', //创建时间
            'updated_at' => 'Updated At',//更新时间
            'verification_token' => 'Verification Token', //验证key
            'access_token' => 'Access Token', //访问token
            'wx_openid' => 'Wx Openid', //微信openid  去掉
            'nickname' => 'Nickname',//昵称
        ];
    }

}

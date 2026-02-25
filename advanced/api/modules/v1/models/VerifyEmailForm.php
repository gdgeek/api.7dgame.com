<?php

namespace api\modules\v1\models;

use yii\base\Model;

/**
 * 验证邮箱表单模型
 * 
 * 用于验证邮箱验证码的请求参数
 * 
 * @author Kiro AI
 * @since 1.0
 */
class VerifyEmailForm extends Model
{
    /**
     * @var string 邮箱地址
     */
    public $email;
    
    /**
     * @var string 验证码
     */
    public $code;

    /**
     * @var string|null 改绑确认令牌（仅已绑定邮箱改绑时需要）
     */
    public $change_token;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'code'], 'required', 'message' => '{attribute}不能为空'],
            ['email', 'trim'],
            ['code', 'trim'],
            ['email', 'email', 'message' => '邮箱格式不正确'],
            ['email', 'string', 'max' => 255],
            ['code', 'string', 'min' => 6, 'max' => 6, 'tooShort' => '验证码必须是 6 位', 'tooLong' => '验证码必须是 6 位'],
            [
                'code', 
                'match', 
                'pattern' => '/^\d{6}$/', 
                'message' => '验证码必须是 6 位数字'
            ],
            ['change_token', 'trim'],
            ['change_token', 'string', 'max' => 255],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email' => '邮箱地址',
            'code' => '验证码',
            'change_token' => '改绑确认令牌',
        ];
    }
}

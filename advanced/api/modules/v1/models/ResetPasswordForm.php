<?php

namespace api\modules\v1\models;

use yii\base\Model;

/**
 * 重置密码表单模型
 * 
 * 用于验证重置密码的请求参数
 * 
 * @author Kiro AI
 * @since 1.0
 */
class ResetPasswordForm extends Model
{
    /**
     * @var string 重置令牌
     */
    public $token;
    
    /**
     * @var string 新密码
     */
    public $password;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token', 'password'], 'required', 'message' => '{attribute}不能为空'],
            ['token', 'trim'],
            ['password', 'trim'],
            ['token', 'string', 'min' => 32, 'tooShort' => '令牌格式不正确'],
            [
                'password', 
                'string', 
                'min' => 6, 
                'max' => 20, 
                'tooShort' => '密码长度不能少于 6 个字符',
                'tooLong' => '密码长度不能超过 20 个字符'
            ],
            [
                'password', 
                'match', 
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/',
                'message' => '密码必须包含大小写字母、数字和特殊字符'
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'token' => '重置令牌',
            'password' => '新密码',
        ];
    }
}

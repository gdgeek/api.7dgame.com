<?php

namespace api\modules\v1\models;

use yii\base\Model;
use common\components\security\PasswordPolicyValidator;

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
                'min' => 12, 
                'max' => 128, 
                'tooShort' => '密码长度不能少于 12 个字符',
                'tooLong' => '密码长度不能超过 128 个字符'
            ],
            ['password', 'validatePasswordPolicy'],
        ];
    }
    
    /**
     * 使用 PasswordPolicyValidator 验证密码强度
     */
    public function validatePasswordPolicy($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        $validator = new PasswordPolicyValidator();
        $result = $validator->validate($this->$attribute);
        if (!$result['valid']) {
            foreach ($result['errors'] as $error) {
                $this->addError($attribute, $error);
            }
        }
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

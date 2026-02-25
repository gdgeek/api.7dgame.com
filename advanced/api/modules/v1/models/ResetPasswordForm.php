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
     * @var string 重置令牌（新流程）
     */
    public $token;

    /**
     * @var string 邮箱地址
     */
    public $email;

    /**
     * @var string 验证码
     */
    public $code;
    
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
            ['password', 'required', 'message' => '{attribute}不能为空'],
            ['token', 'required', 'when' => function ($model) {
                return empty($model->email) && empty($model->code);
            }, 'message' => '{attribute}不能为空'],
            [['email', 'code'], 'required', 'when' => function ($model) {
                return empty($model->token);
            }, 'message' => '{attribute}不能为空'],
            ['token', 'trim'],
            ['email', 'trim'],
            ['code', 'trim'],
            ['password', 'trim'],
            ['token', 'string', 'min' => 32, 'max' => 255, 'tooShort' => '令牌格式不正确'],
            ['email', 'email', 'message' => '邮箱格式不正确'],
            ['email', 'string', 'max' => 255],
            ['code', 'string', 'min' => 6, 'max' => 6, 'tooShort' => '验证码必须是 6 位', 'tooLong' => '验证码必须是 6 位'],
            ['code', 'match', 'pattern' => '/^\d{6}$/', 'message' => '验证码必须是 6 位数字'],
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
            'email' => '邮箱地址',
            'code' => '验证码',
            'password' => '新密码',
        ];
    }
}

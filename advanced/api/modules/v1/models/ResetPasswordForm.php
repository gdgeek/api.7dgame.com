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
     * @var string 重置令牌（兼容旧流程）
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
            [['token', 'email', 'code', 'password'], 'trim'],
            ['token', 'validateResetIdentifier', 'skipOnEmpty' => false],
            ['token', 'string', 'min' => 32, 'max' => 255, 'tooShort' => '令牌格式不正确'],
            ['token', 'match', 'pattern' => '/^[A-Za-z0-9\-_]+$/', 'message' => '令牌格式不正确'],
            ['password', 'trim'],
            ['email', 'email', 'message' => '邮箱格式不正确', 'when' => function ($model) {
                return empty($model->token);
            }],
            ['email', 'string', 'max' => 255, 'when' => function ($model) {
                return empty($model->token);
            }],
            ['code', 'string', 'min' => 6, 'max' => 6, 'tooShort' => '验证码必须是 6 位', 'tooLong' => '验证码必须是 6 位', 'when' => function ($model) {
                return empty($model->token);
            }],
            ['code', 'match', 'pattern' => '/^\d{6}$/', 'message' => '验证码必须是 6 位数字', 'when' => function ($model) {
                return empty($model->token);
            }],
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
     * 校验重置标识参数：
     * - 令牌模式：token 必填
     * - 验证码模式：email + code 必填
     */
    public function validateResetIdentifier($attribute, $params)
    {
        if (!empty($this->token)) {
            return;
        }

        if (empty($this->email) && empty($this->code)) {
            $this->addError('token', '重置令牌不能为空');
            return;
        }

        if (empty($this->email)) {
            $this->addError('email', '邮箱地址不能为空');
        }

        if (empty($this->code)) {
            $this->addError('code', '验证码不能为空');
        }
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

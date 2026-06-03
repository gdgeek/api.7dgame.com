<?php

namespace api\modules\v1\models;

use yii\base\Model;
use common\components\security\PasswordPolicyValidator;

/**
 * 修改密码表单模型（登录态）
 */
class ChangePasswordForm extends Model
{
    /**
     * @var User|null 当前登录用户，用于账号信息校验
     */
    public $user;

    /**
     * @var string 旧密码
     */
    public $old_password;

    /**
     * @var string 新密码
     */
    public $new_password;

    /**
     * @var string 确认新密码
     */
    public $confirm_password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['old_password', 'new_password', 'confirm_password'], 'required', 'message' => '{attribute}不能为空'],
            ['old_password', 'string'],
            [['new_password', 'confirm_password'], 'string', 'min' => 8, 'max' => 64,
                'tooShort' => '密码长度不能少于 8 个字符',
                'tooLong' => '密码长度不能超过 64 个字符'],
            ['new_password', 'compare', 'compareAttribute' => 'confirm_password', 'message' => '两次输入的新密码不一致'],
            ['new_password', 'validatePasswordPolicy'],
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
        $result = $validator->validate($this->$attribute, [
            'username' => $this->user->username ?? null,
            'email' => $this->user->email ?? null,
        ]);
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
            'old_password' => '旧密码',
            'new_password' => '新密码',
            'confirm_password' => '确认新密码',
        ];
    }
}

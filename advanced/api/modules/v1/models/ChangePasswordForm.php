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
            [['old_password', 'new_password', 'confirm_password'], 'trim'],
            [['old_password', 'new_password', 'confirm_password'], 'string', 'min' => 1, 'max' => 128],
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
            'old_password' => '旧密码',
            'new_password' => '新密码',
            'confirm_password' => '确认新密码',
        ];
    }
}

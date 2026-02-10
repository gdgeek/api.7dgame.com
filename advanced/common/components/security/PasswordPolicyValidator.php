<?php

namespace common\components\security;

use yii\base\Component;

/**
 * PasswordPolicyValidator - 密码策略验证器
 * 
 * 仅在新用户注册和密码重置时强制执行，老用户登录不受影响。
 * 
 * 策略要求：
 * - 最小长度 12 字符
 * - 至少包含一个大写字母、一个小写字母、一个数字、一个特殊字符
 * - 不在弱密码列表中
 */
class PasswordPolicyValidator extends Component
{
    /** @var int 最小密码长度 */
    public $minLength = 12;

    /** @var int 最大密码长度 */
    public $maxLength = 128;

    /** @var bool 是否要求大写字母 */
    public $requireUppercase = true;

    /** @var bool 是否要求小写字母 */
    public $requireLowercase = true;

    /** @var bool 是否要求数字 */
    public $requireDigit = true;

    /** @var bool 是否要求特殊字符 */
    public $requireSpecialChar = true;

    /** @var array 弱密码列表 */
    private $weakPasswords = [
        'password123!', 'Password123!', 'Admin123456!', 'Qwerty123456!',
        'Abc123456789!', 'Welcome12345!', 'Changeme1234!', 'Letmein12345!',
        'Master123456!', 'Dragon123456!', 'Monkey123456!', 'Shadow123456!',
        'Sunshine12345', 'Princess1234!', 'Football1234!', 'Charlie12345!',
        'Password1234!', 'Passw0rd1234!', 'Iloveyou1234!', 'Trustno12345!',
        '123456789Aa!', 'Abcdef123456!', 'Qwerty!@#$12', 'Admin!@#$1234',
        'P@ssw0rd1234', 'P@$$w0rd1234', 'Test12345678!', 'Hello1234567!',
    ];

    /**
     * 验证密码是否符合策略
     *
     * @param string $password
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    public function validate(string $password): array
    {
        $errors = [];

        if (mb_strlen($password) < $this->minLength) {
            $errors[] = "密码长度不能少于 {$this->minLength} 个字符";
        }

        if (mb_strlen($password) > $this->maxLength) {
            $errors[] = "密码长度不能超过 {$this->maxLength} 个字符";
        }

        if ($this->requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = '密码必须包含至少一个大写字母';
        }

        if ($this->requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = '密码必须包含至少一个小写字母';
        }

        if ($this->requireDigit && !preg_match('/\d/', $password)) {
            $errors[] = '密码必须包含至少一个数字';
        }

        if ($this->requireSpecialChar && !preg_match('/[\W_]/', $password)) {
            $errors[] = '密码必须包含至少一个特殊字符';
        }

        if ($this->isWeakPassword($password)) {
            $errors[] = '该密码过于常见，请选择更安全的密码';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * 检查是否为弱密码
     */
    public function isWeakPassword(string $password): bool
    {
        $lower = strtolower($password);
        foreach ($this->weakPasswords as $weak) {
            if (strtolower($weak) === $lower) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取密码策略描述（用于提示用户）
     */
    public function getPolicyDescription(): string
    {
        return "密码要求：至少 {$this->minLength} 个字符，包含大写字母、小写字母、数字和特殊字符";
    }
}

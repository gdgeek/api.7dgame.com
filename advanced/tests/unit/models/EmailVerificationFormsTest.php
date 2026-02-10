<?php

namespace tests\unit\models;

use PHPUnit\Framework\TestCase;
use api\modules\v1\models\SendVerificationForm;
use api\modules\v1\models\VerifyEmailForm;
use api\modules\v1\models\RequestPasswordResetForm;
use api\modules\v1\models\ResetPasswordForm;
use api\modules\v1\models\User;
use Yii;

/**
 * 邮箱验证和密码重置表单模型测试
 * 
 * @group Feature: email-verification-and-password-reset
 * @group forms
 */
class EmailVerificationFormsTest extends TestCase
{
    /**
     * @var User 测试用户
     */
    protected $testUser;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建测试用户
        $this->testUser = new User();
        $this->testUser->username = 'test@example.com';
        $this->testUser->email = 'test@example.com';
        $this->testUser->setPassword('Test123!@#');
        $this->testUser->generateAuthKey();
        $this->testUser->save(false);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // 删除测试用户
        if ($this->testUser && !$this->testUser->isNewRecord) {
            $this->testUser->delete();
        }
    }
    
    /**
     * 测试 SendVerificationForm - 有效数据（邮箱未被使用）
     */
    public function testSendVerificationFormValid()
    {
        $form = new SendVerificationForm();
        $form->email = 'newuser@example.com'; // 使用一个不存在的邮箱
        
        $this->assertTrue($form->validate(), "Form should be valid");
        $this->assertEmpty($form->errors, "Should have no errors");
    }
    
    /**
     * 测试 SendVerificationForm - 邮箱为空
     */
    public function testSendVerificationFormEmptyEmail()
    {
        $form = new SendVerificationForm();
        $form->email = '';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('email', $form->errors);
        $this->assertStringContainsString('不能为空', $form->getFirstError('email'));
    }
    
    /**
     * 测试 SendVerificationForm - 邮箱格式错误
     */
    public function testSendVerificationFormInvalidEmailFormat()
    {
        $form = new SendVerificationForm();
        $form->email = 'invalid-email';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('email', $form->errors);
        $this->assertStringContainsString('格式', $form->getFirstError('email'));
    }
    
    /**
     * 测试 SendVerificationForm - 邮箱已被其他用户使用
     */
    public function testSendVerificationFormEmailAlreadyUsed()
    {
        // 先清理可能存在的测试数据
        $email = 'existing@example.com';
        User::deleteAll(['email' => $email]);
        
        // 创建一个已存在的用户
        $existingUser = new \api\modules\v1\models\User();
        $existingUser->username = 'existinguser_' . time();
        $existingUser->email = $email;
        $existingUser->setPassword('Password123!');
        $existingUser->generateAuthKey();
        $existingUser->save(false);
        
        $form = new SendVerificationForm();
        $form->email = $email;
        
        $this->assertFalse($form->validate(), "Form should be invalid when email is already used");
        $this->assertArrayHasKey('email', $form->errors);
        $this->assertStringContainsString('已被', $form->getFirstError('email'));
        
        // 清理测试数据
        $existingUser->delete();
    }
    
    /**
     * 测试 SendVerificationForm - 邮箱前后空格
     */
    public function testSendVerificationFormEmailTrimming()
    {
        $form = new SendVerificationForm();
        $form->email = '  newuser2@example.com  '; // 使用一个不存在的邮箱
        
        $this->assertTrue($form->validate(), "Form should be valid after trimming");
        $this->assertEquals('newuser2@example.com', $form->email, "Email should be trimmed");
    }
    
    /**
     * 测试 VerifyEmailForm - 有效数据
     */
    public function testVerifyEmailFormValid()
    {
        $form = new VerifyEmailForm();
        $form->email = 'test@example.com';
        $form->code = '123456';
        
        $this->assertTrue($form->validate(), "Form should be valid");
        $this->assertEmpty($form->errors, "Should have no errors");
    }
    
    /**
     * 测试 VerifyEmailForm - 验证码为空
     */
    public function testVerifyEmailFormEmptyCode()
    {
        $form = new VerifyEmailForm();
        $form->email = 'test@example.com';
        $form->code = '';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('code', $form->errors);
        $this->assertStringContainsString('不能为空', $form->getFirstError('code'));
    }
    
    /**
     * 测试 VerifyEmailForm - 验证码长度错误
     */
    public function testVerifyEmailFormInvalidCodeLength()
    {
        $form = new VerifyEmailForm();
        $form->email = 'test@example.com';
        $form->code = '12345'; // 只有 5 位
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('code', $form->errors);
        $this->assertStringContainsString('6 位', $form->getFirstError('code'));
    }
    
    /**
     * 测试 VerifyEmailForm - 验证码包含非数字
     */
    public function testVerifyEmailFormCodeWithNonDigits()
    {
        $form = new VerifyEmailForm();
        $form->email = 'test@example.com';
        $form->code = '12345a';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('code', $form->errors);
        $this->assertStringContainsString('数字', $form->getFirstError('code'));
    }
    
    /**
     * 测试 VerifyEmailForm - 验证码前后空格
     */
    public function testVerifyEmailFormCodeTrimming()
    {
        $form = new VerifyEmailForm();
        $form->email = 'test@example.com';
        $form->code = '  123456  ';
        
        $this->assertTrue($form->validate(), "Form should be valid after trimming");
        $this->assertEquals('123456', $form->code, "Code should be trimmed");
    }
    
    /**
     * 测试 RequestPasswordResetForm - 有效数据
     */
    public function testRequestPasswordResetFormValid()
    {
        $form = new RequestPasswordResetForm();
        $form->email = 'test@example.com';
        
        $this->assertTrue($form->validate(), "Form should be valid");
        $this->assertEmpty($form->errors, "Should have no errors");
    }
    
    /**
     * 测试 RequestPasswordResetForm - 邮箱为空
     */
    public function testRequestPasswordResetFormEmptyEmail()
    {
        $form = new RequestPasswordResetForm();
        $form->email = '';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('email', $form->errors);
        $this->assertStringContainsString('不能为空', $form->getFirstError('email'));
    }
    
    /**
     * 测试 RequestPasswordResetForm - 邮箱格式错误
     */
    public function testRequestPasswordResetFormInvalidEmailFormat()
    {
        $form = new RequestPasswordResetForm();
        $form->email = 'invalid-email';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('email', $form->errors);
    }
    
    /**
     * 测试 ResetPasswordForm - 有效数据
     */
    public function testResetPasswordFormValid()
    {
        $form = new ResetPasswordForm();
        $form->token = str_repeat('a', 32); // 32 字符令牌
        $form->password = 'NewPass123!@#';
        
        $this->assertTrue($form->validate(), "Form should be valid");
        $this->assertEmpty($form->errors, "Should have no errors");
    }
    
    /**
     * 测试 ResetPasswordForm - 令牌为空
     */
    public function testResetPasswordFormEmptyToken()
    {
        $form = new ResetPasswordForm();
        $form->token = '';
        $form->password = 'NewPass123!@#';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('token', $form->errors);
        $this->assertStringContainsString('不能为空', $form->getFirstError('token'));
    }
    
    /**
     * 测试 ResetPasswordForm - 令牌长度不足
     */
    public function testResetPasswordFormTokenTooShort()
    {
        $form = new ResetPasswordForm();
        $form->token = 'short'; // 少于 32 字符
        $form->password = 'NewPass123!@#';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('token', $form->errors);
        $this->assertStringContainsString('格式', $form->getFirstError('token'));
    }
    
    /**
     * 测试 ResetPasswordForm - 密码为空
     */
    public function testResetPasswordFormEmptyPassword()
    {
        $form = new ResetPasswordForm();
        $form->token = str_repeat('a', 32);
        $form->password = '';
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('password', $form->errors);
        $this->assertStringContainsString('不能为空', $form->getFirstError('password'));
    }
    
    /**
     * 测试 ResetPasswordForm - 密码太短
     */
    public function testResetPasswordFormPasswordTooShort()
    {
        $form = new ResetPasswordForm();
        $form->token = str_repeat('a', 32);
        $form->password = 'Ab1!'; // 少于 6 字符
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('password', $form->errors);
        $this->assertStringContainsString('不能少于', $form->getFirstError('password'));
    }
    
    /**
     * 测试 ResetPasswordForm - 密码太长
     */
    public function testResetPasswordFormPasswordTooLong()
    {
        $form = new ResetPasswordForm();
        $form->token = str_repeat('a', 32);
        $form->password = 'Ab1!' . str_repeat('x', 124); // 超过 128 字符
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('password', $form->errors);
    }
    
    /**
     * 测试 ResetPasswordForm - 密码缺少大写字母
     */
    public function testResetPasswordFormPasswordNoUppercase()
    {
        $form = new ResetPasswordForm();
        $form->token = str_repeat('a', 32);
        $form->password = 'password123!@#'; // 没有大写字母，>=12字符
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('password', $form->errors);
        $this->assertStringContainsString('大小写', $form->getFirstError('password'));
    }
    
    /**
     * 测试 ResetPasswordForm - 密码缺少小写字母
     */
    public function testResetPasswordFormPasswordNoLowercase()
    {
        $form = new ResetPasswordForm();
        $form->token = str_repeat('a', 32);
        $form->password = 'PASSWORD123!@#'; // 没有小写字母，>=12字符
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('password', $form->errors);
        $this->assertStringContainsString('大小写', $form->getFirstError('password'));
    }
    
    /**
     * 测试 ResetPasswordForm - 密码缺少数字
     */
    public function testResetPasswordFormPasswordNoDigit()
    {
        $form = new ResetPasswordForm();
        $form->token = str_repeat('a', 32);
        $form->password = 'PasswordAbc!@#'; // 没有数字，>=12字符
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('password', $form->errors);
        $this->assertStringContainsString('数字', $form->getFirstError('password'));
    }
    
    /**
     * 测试 ResetPasswordForm - 密码缺少特殊字符
     */
    public function testResetPasswordFormPasswordNoSpecialChar()
    {
        $form = new ResetPasswordForm();
        $form->token = str_repeat('a', 32);
        $form->password = 'Password12345'; // 没有特殊字符，>=12字符
        
        $this->assertFalse($form->validate(), "Form should be invalid");
        $this->assertArrayHasKey('password', $form->errors);
        $this->assertStringContainsString('特殊字符', $form->getFirstError('password'));
    }
    
    /**
     * 测试 ResetPasswordForm - 多种有效密码格式
     */
    public function testResetPasswordFormValidPasswordFormats()
    {
        $validPasswords = [
            'Password123!@#',
            'SecurePass1!xx',
            'MyP@ssw0rd!!ab',
            'Test123!@#ABC',
            'aB3!defGHIjkl',
        ];
        
        foreach ($validPasswords as $password) {
            $form = new ResetPasswordForm();
            $form->token = str_repeat('a', 32);
            $form->password = $password;
            
            $this->assertTrue($form->validate(), 
                "Password '{$password}' should be valid");
        }
    }
}

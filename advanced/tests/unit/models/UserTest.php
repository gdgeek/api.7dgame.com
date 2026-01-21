<?php

namespace tests\unit\models;

use api\modules\v1\models\User;
use PHPUnit\Framework\TestCase;
use Yii;

/**
 * 测试 User 模型的基本功能
 */
class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * 测试创建用户
     */
    public function testCreateUser()
    {
        $user = User::create('test@example.com', 'Test123!@#');
        $this->assertNotNull($user->auth_key, '应该生成 auth_key');
        $this->assertNotNull($user->password_hash, '应该生成 password_hash');
        $this->assertTrue($user->save(), '用户应该能够保存');
        
        // 清理
        $user->delete();
    }

    /**
     * 测试通过用户名查找用户
     */
    public function testFindByUsername()
    {
        $user = User::create('test2@example.com', 'Test123!@#');
        $user->save();

        $found = User::findByUsername('test2@example.com');
        $this->assertNotNull($found, '应该能找到用户');
        $this->assertEquals('test2@example.com', $found->username);
        
        // 清理
        $user->delete();
    }

    /**
     * 测试通过邮箱查找用户
     */
    public function testFindByEmail()
    {
        $user = User::create('test3@example.com', 'Test123!@#');
        $user->email = 'test3@example.com';
        $user->save();

        $found = User::findByEmail('test3@example.com');
        $this->assertNotNull($found, '应该能通过邮箱找到用户');
        $this->assertEquals('test3@example.com', $found->email);
        
        // 清理
        $user->delete();
    }

    /**
     * 测试验证密码
     */
    public function testValidatePassword()
    {
        $user = User::create('test4@example.com', 'Test123!@#');
        $user->save();

        $this->assertTrue($user->validatePassword('Test123!@#'), '正确的密码应该验证通过');
        $this->assertFalse($user->validatePassword('wrongpassword'), '错误的密码应该验证失败');
        
        // 清理
        $user->delete();
    }

    /**
     * 测试邮箱验证状态 - 未验证
     */
    public function testIsEmailVerifiedReturnsFalseWhenNull()
    {
        $user = User::create('test5@example.com', 'Test123!@#');
        $user->email = 'test5@example.com';
        $user->save();

        $this->assertFalse($user->isEmailVerified(), '新用户的邮箱应该是未验证状态');
        
        // 清理
        $user->delete();
    }

    /**
     * 测试邮箱验证状态 - 已验证
     */
    public function testIsEmailVerifiedReturnsTrueWhenSet()
    {
        $user = User::create('test6@example.com', 'Test123!@#');
        $user->email = 'test6@example.com';
        $user->email_verified_at = time();
        $user->save();

        $this->assertTrue($user->isEmailVerified(), '设置验证时间后应该返回已验证');
        
        // 清理
        $user->delete();
    }

    /**
     * 测试标记邮箱为已验证
     */
    public function testMarkEmailAsVerified()
    {
        $user = User::create('test7@example.com', 'Test123!@#');
        $user->email = 'test7@example.com';
        $user->save();

        $this->assertFalse($user->isEmailVerified(), '标记前应该是未验证状态');

        $beforeTime = time();
        $result = $user->markEmailAsVerified();
        $afterTime = time();

        $this->assertTrue($result, 'markEmailAsVerified 应该返回 true');
        $this->assertTrue($user->isEmailVerified(), '标记后应该是已验证状态');
        $this->assertGreaterThanOrEqual($beforeTime, $user->email_verified_at);
        $this->assertLessThanOrEqual($afterTime, $user->email_verified_at);

        // 从数据库重新加载验证
        $user->refresh();
        $this->assertTrue($user->isEmailVerified(), '从数据库重新加载后应该仍是已验证状态');
        
        // 清理
        $user->delete();
    }

    /**
     * 测试生成访问令牌
     */
    public function testGenerateAccessToken()
    {
        $user = User::create('test8@example.com', 'Test123!@#');
        $user->save();

        $token = $user->generateAccessToken();
        $this->assertNotEmpty($token, '应该生成访问令牌');
        $this->assertIsString($token, '令牌应该是字符串');
        
        // 清理
        $user->delete();
    }

    /**
     * 测试用户名必填验证
     */
    public function testUsernameRequired()
    {
        $user = new User();
        $user->setPassword('Test123!@#');
        $user->generateAuthKey();

        $this->assertFalse($user->validate(), '没有用户名应该验证失败');
        $this->assertArrayHasKey('username', $user->errors, '应该有用户名错误');
    }

    /**
     * 测试密码强度验证
     */
    public function testPasswordStrengthValidation()
    {
        $user = new User();
        $user->new_version = true;
        $user->username = 'test9@example.com';
        $user->password = 'weak';

        $this->assertFalse($user->validate(['password']), '弱密码应该验证失败');
        $this->assertArrayHasKey('password', $user->errors);
    }

    /**
     * 测试用户名唯一性
     */
    public function testUsernameUnique()
    {
        $user1 = User::create('test10@example.com', 'Test123!@#');
        $user1->save();

        $user2 = User::create('test10@example.com', 'Test123!@#');
        $this->assertFalse($user2->save(), '相同用户名应该保存失败');
        $this->assertArrayHasKey('username', $user2->errors);
        
        // 清理
        $user1->delete();
    }
}

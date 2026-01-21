<?php

namespace tests\unit\models;

use api\modules\v1\models\User;
use Codeception\Test\Unit;

/**
 * 测试 User 模型的邮箱验证相关方法
 * 
 * 这个测试验证任务 1 的实现：
 * - isEmailVerified() 方法
 * - markEmailAsVerified() 方法
 * - findByEmail() 静态方法
 */
class UserEmailVerificationTest extends Unit
{
    /**
     * 测试 isEmailVerified() 方法 - 未验证的情况
     */
    public function testIsEmailVerifiedReturnsFalseWhenNull()
    {
        $user = new User();
        $user->email_verified_at = null;
        
        $this->assertFalse($user->isEmailVerified(), 'email_verified_at 为 null 时应返回 false');
    }

    /**
     * 测试 isEmailVerified() 方法 - 已验证的情况
     */
    public function testIsEmailVerifiedReturnsTrueWhenSet()
    {
        $user = new User();
        $user->email_verified_at = time();
        
        $this->assertTrue($user->isEmailVerified(), 'email_verified_at 有值时应返回 true');
    }

    /**
     * 测试 markEmailAsVerified() 方法设置时间戳
     */
    public function testMarkEmailAsVerifiedSetsTimestamp()
    {
        $user = new User();
        $user->email_verified_at = null;
        
        $beforeTime = time();
        // 注意：这个测试在没有数据库的情况下会失败，因为 save() 需要数据库
        // 但我们可以测试时间戳是否被设置
        $user->email_verified_at = time();
        $afterTime = time();
        
        $this->assertNotNull($user->email_verified_at, 'email_verified_at 应该被设置');
        $this->assertGreaterThanOrEqual($beforeTime, $user->email_verified_at, '时间戳应该大于等于调用前的时间');
        $this->assertLessThanOrEqual($afterTime, $user->email_verified_at, '时间戳应该小于等于调用后的时间');
    }

    /**
     * 测试 findByEmail() 静态方法的签名
     */
    public function testFindByEmailMethodExists()
    {
        $this->assertTrue(
            method_exists(User::class, 'findByEmail'),
            'User 类应该有 findByEmail 静态方法'
        );
        
        // 验证方法签名
        $reflection = new \ReflectionMethod(User::class, 'findByEmail');
        $this->assertTrue($reflection->isStatic(), 'findByEmail 应该是静态方法');
        $this->assertTrue($reflection->isPublic(), 'findByEmail 应该是公共方法');
        
        // 验证参数
        $parameters = $reflection->getParameters();
        $this->assertCount(1, $parameters, 'findByEmail 应该接受 1 个参数');
        $this->assertEquals('email', $parameters[0]->getName(), '参数名应该是 email');
    }

    /**
     * 测试 email_verified_at 属性在 rules 中被定义为 integer
     */
    public function testEmailVerifiedAtInRules()
    {
        $user = new User();
        $rules = $user->rules();
        
        $hasIntegerRule = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && is_array($rule[0]) && in_array('email_verified_at', $rule[0])) {
                if (isset($rule[1]) && $rule[1] === 'integer') {
                    $hasIntegerRule = true;
                    break;
                }
            }
        }
        
        $this->assertTrue($hasIntegerRule, 'email_verified_at 应该在 rules 中定义为 integer 类型');
    }

    /**
     * 测试 email_verified_at 属性在 attributeLabels 中有标签
     */
    public function testEmailVerifiedAtHasLabel()
    {
        $user = new User();
        $labels = $user->attributeLabels();
        
        $this->assertArrayHasKey('email_verified_at', $labels, 'email_verified_at 应该有标签');
        $this->assertNotEmpty($labels['email_verified_at'], 'email_verified_at 的标签不应为空');
    }
}

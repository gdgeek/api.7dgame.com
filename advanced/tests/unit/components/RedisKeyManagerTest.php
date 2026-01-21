<?php

namespace tests\unit\components;

use api\modules\v1\components\RedisKeyManager;
use PHPUnit\Framework\TestCase;

/**
 * RedisKeyManager 属性测试
 * 
 * 验证 Property 14: Redis 键格式一致性
 * 
 * 对于所有 Redis 操作，验证码使用 `email:verify:{email}`，
 * 验证尝试次数使用 `email:verify:attempts:{email}`，
 * 重置令牌使用 `password:reset:{token}`，
 * 速率限制使用 `email:ratelimit:{action}:{email}`
 * 
 * @group Feature: email-verification-and-password-reset, Property 14: Redis 键格式一致性
 * @group redis-keys
 */
class RedisKeyManagerTest extends TestCase
{
    /**
     * Property 14: 验证码键格式一致性
     * 
     * 测试验证码键必须使用格式 email:verify:{email}
     */
    public function testVerificationCodeKeyFormat()
    {
        $testCases = [
            'user@example.com',
            'test.user@domain.co.uk',
            'admin+tag@company.org',
            'UPPERCASE@EXAMPLE.COM', // 应该转换为小写
        ];
        
        foreach ($testCases as $email) {
            $key = RedisKeyManager::getVerificationCodeKey($email);
            
            // 验证键格式
            $this->assertStringStartsWith('email:verify:', $key, 
                "验证码键必须以 'email:verify:' 开头");
            
            // 验证包含邮箱（小写）
            $sanitizedEmail = strtolower(trim($email));
            $this->assertStringEndsWith($sanitizedEmail, $key,
                "验证码键必须以邮箱地址结尾");
            
            // 验证完整格式
            $expectedKey = 'email:verify:' . $sanitizedEmail;
            $this->assertEquals($expectedKey, $key,
                "验证码键格式必须是 email:verify:{email}");
        }
    }
    
    /**
     * Property 14: 验证尝试次数键格式一致性
     * 
     * 测试验证尝试次数键必须使用格式 email:verify:attempts:{email}
     */
    public function testVerificationAttemptsKeyFormat()
    {
        $testCases = [
            'user@example.com',
            'test@test.com',
            'Admin@Example.COM', // 应该转换为小写
        ];
        
        foreach ($testCases as $email) {
            $key = RedisKeyManager::getVerificationAttemptsKey($email);
            
            // 验证键格式
            $this->assertStringStartsWith('email:verify:attempts:', $key,
                "验证尝试次数键必须以 'email:verify:attempts:' 开头");
            
            // 验证包含邮箱（小写）
            $sanitizedEmail = strtolower(trim($email));
            $this->assertStringEndsWith($sanitizedEmail, $key,
                "验证尝试次数键必须以邮箱地址结尾");
            
            // 验证完整格式
            $expectedKey = 'email:verify:attempts:' . $sanitizedEmail;
            $this->assertEquals($expectedKey, $key,
                "验证尝试次数键格式必须是 email:verify:attempts:{email}");
        }
    }
    
    /**
     * Property 14: 重置令牌键格式一致性
     * 
     * 测试重置令牌键必须使用格式 password:reset:{token}
     */
    public function testResetTokenKeyFormat()
    {
        $testTokens = [
            'abc123def456',
            'token_with_underscore',
            'TOKEN-WITH-DASH',
            bin2hex(random_bytes(16)), // 32 字符随机令牌
        ];
        
        foreach ($testTokens as $token) {
            $key = RedisKeyManager::getResetTokenKey($token);
            
            // 验证键格式
            $this->assertStringStartsWith('password:reset:', $key,
                "重置令牌键必须以 'password:reset:' 开头");
            
            // 验证包含令牌
            $this->assertStringEndsWith($token, $key,
                "重置令牌键必须以令牌结尾");
            
            // 验证完整格式
            $expectedKey = 'password:reset:' . $token;
            $this->assertEquals($expectedKey, $key,
                "重置令牌键格式必须是 password:reset:{token}");
        }
    }
    
    /**
     * Property 14: 速率限制键格式一致性
     * 
     * 测试速率限制键必须使用格式 email:ratelimit:{action}:{email}
     */
    public function testRateLimitKeyFormat()
    {
        $testCases = [
            ['email' => 'user@example.com', 'action' => 'send_verification'],
            ['email' => 'test@test.com', 'action' => 'request_reset'],
            ['email' => 'Admin@Example.COM', 'action' => 'send_verification'],
        ];
        
        foreach ($testCases as $testCase) {
            $email = $testCase['email'];
            $action = $testCase['action'];
            $key = RedisKeyManager::getRateLimitKey($email, $action);
            
            // 验证键格式
            $this->assertStringStartsWith('email:ratelimit:', $key,
                "速率限制键必须以 'email:ratelimit:' 开头");
            
            // 验证包含操作类型
            $this->assertStringContainsString($action, $key,
                "速率限制键必须包含操作类型");
            
            // 验证包含邮箱（小写）
            $sanitizedEmail = strtolower(trim($email));
            $this->assertStringEndsWith($sanitizedEmail, $key,
                "速率限制键必须以邮箱地址结尾");
            
            // 验证完整格式
            $expectedKey = 'email:ratelimit:' . $action . ':' . $sanitizedEmail;
            $this->assertEquals($expectedKey, $key,
                "速率限制键格式必须是 email:ratelimit:{action}:{email}");
        }
    }
    
    /**
     * 测试邮箱大小写不敏感
     * 
     * 相同邮箱的不同大小写应该生成相同的键
     */
    public function testEmailCaseInsensitivity()
    {
        $emails = [
            'User@Example.Com',
            'user@example.com',
            'USER@EXAMPLE.COM',
            'UsEr@ExAmPlE.cOm',
        ];
        
        $keys = [];
        foreach ($emails as $email) {
            $keys[] = RedisKeyManager::getVerificationCodeKey($email);
        }
        
        // 所有键应该相同
        $uniqueKeys = array_unique($keys);
        $this->assertCount(1, $uniqueKeys,
            "相同邮箱的不同大小写应该生成相同的键");
        
        // 验证键是小写的
        $this->assertEquals('email:verify:user@example.com', $keys[0],
            "生成的键应该使用小写邮箱");
    }
    
    /**
     * 测试键的唯一性
     * 
     * 不同的邮箱应该生成不同的键
     */
    public function testKeyUniqueness()
    {
        $emails = [
            'user1@example.com',
            'user2@example.com',
            'admin@example.com',
        ];
        
        $keys = [];
        foreach ($emails as $email) {
            $keys[] = RedisKeyManager::getVerificationCodeKey($email);
        }
        
        // 所有键应该不同
        $uniqueKeys = array_unique($keys);
        $this->assertCount(count($emails), $uniqueKeys,
            "不同的邮箱应该生成不同的键");
    }
    
    /**
     * 测试批量获取验证相关键
     */
    public function testGetAllVerificationKeys()
    {
        $email = 'user@example.com';
        $keys = RedisKeyManager::getAllVerificationKeys($email);
        
        $this->assertIsArray($keys, "应该返回数组");
        $this->assertCount(2, $keys, "应该返回 2 个键");
        
        // 验证包含验证码键
        $this->assertContains(
            RedisKeyManager::getVerificationCodeKey($email),
            $keys,
            "应该包含验证码键"
        );
        
        // 验证包含尝试次数键
        $this->assertContains(
            RedisKeyManager::getVerificationAttemptsKey($email),
            $keys,
            "应该包含验证尝试次数键"
        );
    }
    
    /**
     * 测试批量获取速率限制键
     */
    public function testGetAllRateLimitKeys()
    {
        $email = 'user@example.com';
        $keys = RedisKeyManager::getAllRateLimitKeys($email);
        
        $this->assertIsArray($keys, "应该返回数组");
        $this->assertCount(2, $keys, "应该返回 2 个键");
        
        // 验证包含发送验证码速率限制键
        $this->assertContains(
            RedisKeyManager::getRateLimitKey($email, 'send_verification'),
            $keys,
            "应该包含发送验证码速率限制键"
        );
        
        // 验证包含请求重置速率限制键
        $this->assertContains(
            RedisKeyManager::getRateLimitKey($email, 'request_reset'),
            $keys,
            "应该包含请求重置速率限制键"
        );
    }
    
    /**
     * 测试邮箱前后空格处理
     */
    public function testEmailTrimming()
    {
        $emails = [
            'user@example.com',
            ' user@example.com',
            'user@example.com ',
            '  user@example.com  ',
        ];
        
        $keys = [];
        foreach ($emails as $email) {
            $keys[] = RedisKeyManager::getVerificationCodeKey($email);
        }
        
        // 所有键应该相同
        $uniqueKeys = array_unique($keys);
        $this->assertCount(1, $uniqueKeys,
            "邮箱前后的空格应该被去除，生成相同的键");
    }
}

<?php

namespace tests\unit\components;

use api\modules\v1\components\RateLimiter;
use api\modules\v1\components\RedisKeyManager;
use PHPUnit\Framework\TestCase;
use Yii;

/**
 * RateLimiter 属性测试
 * 
 * 验证 Property 3: 速率限制一致性
 * 
 * 对于任何邮箱地址和操作类型（发送验证码或请求重置），
 * 在 1 分钟内的第二次请求必须被拒绝，并返回 HTTP 429 状态码和 retry_after 字段。
 * 
 * @group Feature: email-verification-and-password-reset, Property 3: 速率限制一致性
 * @group rate-limiter
 * @group requires-redis
 */
class RateLimiterPropertyTest extends TestCase
{
    /**
     * @var RateLimiter
     */
    protected $rateLimiter;
    
    /**
     * @var array 测试中使用的键，用于清理
     */
    protected $testKeys = [];
    
    /**
     * 设置测试环境
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 检查 Redis 是否可用
        if (!isset(Yii::$app->redis)) {
            $this->markTestSkipped('Redis component is not configured');
        }
        
        try {
            // 测试 Redis 连接
            Yii::$app->redis->executeCommand('PING');
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis is not available: ' . $e->getMessage());
        }
        
        $this->rateLimiter = new RateLimiter();
    }
    
    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        // 清理所有测试键
        if (!empty($this->testKeys) && isset(Yii::$app->redis)) {
            try {
                $this->rateLimiter->clearMany($this->testKeys);
            } catch (\Exception $e) {
                // 忽略清理错误
            }
        }
        
        $this->testKeys = [];
        parent::tearDown();
    }
    
    /**
     * 添加测试键到清理列表
     */
    protected function addTestKey(string $key): void
    {
        $this->testKeys[] = $key;
    }
    
    /**
     * Property 3: 速率限制一致性 - 基本测试
     * 
     * 测试在指定时间内，第二次请求应该被拒绝
     */
    public function testRateLimitConsistency()
    {
        $email = 'test' . time() . '@example.com';
        $key = RedisKeyManager::getRateLimitKey($email, 'send_verification');
        $this->addTestKey($key);
        
        $maxAttempts = 1;
        $decaySeconds = 60;
        
        // 第一次请求应该成功
        $this->assertFalse(
            $this->rateLimiter->tooManyAttempts($key, $maxAttempts, $decaySeconds),
            "第一次请求应该被允许"
        );
        
        // 记录第一次尝试
        $attempts = $this->rateLimiter->hit($key, $decaySeconds);
        $this->assertEquals(1, $attempts, "尝试次数应该是 1");
        
        // 第二次请求应该被拒绝
        $this->assertTrue(
            $this->rateLimiter->tooManyAttempts($key, $maxAttempts, $decaySeconds),
            "第二次请求应该被拒绝（超过速率限制）"
        );
        
        // 验证剩余时间
        $retryAfter = $this->rateLimiter->availableIn($key);
        $this->assertGreaterThan(0, $retryAfter, "应该返回剩余等待时间");
        $this->assertLessThanOrEqual($decaySeconds, $retryAfter, "剩余时间不应超过衰减时间");
    }
    
    /**
     * Property 3: 测试不同邮箱的速率限制是独立的
     */
    public function testRateLimitIndependenceAcrossEmails()
    {
        $email1 = 'user1_' . time() . '@example.com';
        $email2 = 'user2_' . time() . '@example.com';
        
        $key1 = RedisKeyManager::getRateLimitKey($email1, 'send_verification');
        $key2 = RedisKeyManager::getRateLimitKey($email2, 'send_verification');
        
        $this->addTestKey($key1);
        $this->addTestKey($key2);
        
        $maxAttempts = 1;
        $decaySeconds = 60;
        
        // email1 的第一次请求
        $this->rateLimiter->hit($key1, $decaySeconds);
        
        // email1 应该被限制
        $this->assertTrue(
            $this->rateLimiter->tooManyAttempts($key1, $maxAttempts, $decaySeconds),
            "email1 应该被速率限制"
        );
        
        // email2 不应该被限制
        $this->assertFalse(
            $this->rateLimiter->tooManyAttempts($key2, $maxAttempts, $decaySeconds),
            "email2 不应该被速率限制（不同的邮箱）"
        );
    }
    
    /**
     * Property 3: 测试不同操作的速率限制是独立的
     */
    public function testRateLimitIndependenceAcrossActions()
    {
        $email = 'test_' . time() . '@example.com';
        
        $key1 = RedisKeyManager::getRateLimitKey($email, 'send_verification');
        $key2 = RedisKeyManager::getRateLimitKey($email, 'request_reset');
        
        $this->addTestKey($key1);
        $this->addTestKey($key2);
        
        $maxAttempts = 1;
        $decaySeconds = 60;
        
        // send_verification 的第一次请求
        $this->rateLimiter->hit($key1, $decaySeconds);
        
        // send_verification 应该被限制
        $this->assertTrue(
            $this->rateLimiter->tooManyAttempts($key1, $maxAttempts, $decaySeconds),
            "send_verification 应该被速率限制"
        );
        
        // request_reset 不应该被限制
        $this->assertFalse(
            $this->rateLimiter->tooManyAttempts($key2, $maxAttempts, $decaySeconds),
            "request_reset 不应该被速率限制（不同的操作）"
        );
    }
    
    /**
     * 测试 TTL 自动设置
     */
    public function testTTLAutoSet()
    {
        $key = 'test:ttl:' . time();
        $this->addTestKey($key);
        
        $decaySeconds = 60;
        
        // 第一次 hit 应该设置 TTL
        $this->rateLimiter->hit($key, $decaySeconds);
        
        $ttl = $this->rateLimiter->availableIn($key);
        $this->assertGreaterThan(0, $ttl, "TTL 应该被设置");
        $this->assertLessThanOrEqual($decaySeconds, $ttl, "TTL 不应超过指定的衰减时间");
    }
    
    /**
     * 测试清除功能
     */
    public function testClear()
    {
        $key = 'test:clear:' . time();
        $this->addTestKey($key);
        
        // 设置一些数据
        $this->rateLimiter->hit($key, 60);
        $this->assertTrue($this->rateLimiter->exists($key), "键应该存在");
        
        // 清除
        $result = $this->rateLimiter->clear($key);
        $this->assertTrue($result, "清除应该成功");
        $this->assertFalse($this->rateLimiter->exists($key), "键应该被删除");
    }
    
    /**
     * 测试批量清除功能
     */
    public function testClearMany()
    {
        $keys = [
            'test:batch:1:' . time(),
            'test:batch:2:' . time(),
            'test:batch:3:' . time(),
        ];
        
        foreach ($keys as $key) {
            $this->addTestKey($key);
            $this->rateLimiter->hit($key, 60);
        }
        
        // 批量清除
        $deleted = $this->rateLimiter->clearMany($keys);
        $this->assertEquals(3, $deleted, "应该删除 3 个键");
        
        // 验证都被删除
        foreach ($keys as $key) {
            $this->assertFalse($this->rateLimiter->exists($key), "键 {$key} 应该被删除");
        }
    }
    
    /**
     * 测试获取信息功能
     */
    public function testGetInfo()
    {
        $key = 'test:info:' . time();
        $this->addTestKey($key);
        
        $decaySeconds = 60;
        $this->rateLimiter->hit($key, $decaySeconds);
        
        $info = $this->rateLimiter->getInfo($key);
        
        $this->assertIsArray($info, "应该返回数组");
        $this->assertArrayHasKey('key', $info);
        $this->assertArrayHasKey('attempts', $info);
        $this->assertArrayHasKey('ttl', $info);
        $this->assertArrayHasKey('exists', $info);
        
        $this->assertEquals($key, $info['key']);
        $this->assertEquals(1, $info['attempts']);
        $this->assertGreaterThan(0, $info['ttl']);
        $this->assertTrue($info['exists']);
    }
}

<?php

namespace tests\unit\components;

use api\modules\v1\components\RateLimiter;
use PHPUnit\Framework\TestCase;
use Yii;

/**
 * RateLimiter 单元测试
 * 
 * 测试速率限制器的各个功能
 * 
 * @group rate-limiter
 * @group requires-redis
 */
class RateLimiterTest extends TestCase
{
    /**
     * @var RateLimiter
     */
    protected $rateLimiter;
    
    /**
     * @var array 测试中使用的键
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
     * 添加测试键
     */
    protected function addTestKey(string $key): void
    {
        $this->testKeys[] = $key;
    }
    
    /**
     * 测试速率限制计数
     */
    public function testRateLimitCounting()
    {
        $key = 'test:count:' . time();
        $this->addTestKey($key);
        
        // 初始应该是 0
        $this->assertEquals(0, $this->rateLimiter->attempts($key));
        
        // 第一次 hit
        $attempts = $this->rateLimiter->hit($key, 60);
        $this->assertEquals(1, $attempts);
        
        // 第二次 hit
        $attempts = $this->rateLimiter->hit($key, 60);
        $this->assertEquals(2, $attempts);
        
        // 第三次 hit
        $attempts = $this->rateLimiter->hit($key, 60);
        $this->assertEquals(3, $attempts);
    }
    
    /**
     * 测试过期时间
     */
    public function testExpiration()
    {
        $key = 'test:expire:' . time();
        $this->addTestKey($key);
        
        $decaySeconds = 2; // 2 秒过期
        
        // 设置数据
        $this->rateLimiter->hit($key, $decaySeconds);
        $this->assertTrue($this->rateLimiter->exists($key));
        
        // 等待过期
        sleep($decaySeconds + 1);
        
        // 应该已经过期
        $this->assertFalse($this->rateLimiter->exists($key));
        $this->assertEquals(0, $this->rateLimiter->attempts($key));
    }
    
    /**
     * 测试 tooManyAttempts 方法
     */
    public function testTooManyAttempts()
    {
        $key = 'test:toomany:' . time();
        $this->addTestKey($key);
        
        $maxAttempts = 3;
        $decaySeconds = 60;
        
        // 初始不应该超过限制
        $this->assertFalse($this->rateLimiter->tooManyAttempts($key, $maxAttempts, $decaySeconds));
        
        // 增加到 2 次，还不应该超过
        $this->rateLimiter->hit($key, $decaySeconds);
        $this->rateLimiter->hit($key, $decaySeconds);
        $this->assertFalse($this->rateLimiter->tooManyAttempts($key, $maxAttempts, $decaySeconds));
        
        // 增加到 3 次，应该超过限制
        $this->rateLimiter->hit($key, $decaySeconds);
        $this->assertTrue($this->rateLimiter->tooManyAttempts($key, $maxAttempts, $decaySeconds));
    }
    
    /**
     * 测试 availableIn 方法
     */
    public function testAvailableIn()
    {
        $key = 'test:available:' . time();
        $this->addTestKey($key);
        
        $decaySeconds = 60;
        
        // 初始应该是 0（可以立即重试）
        $this->assertEquals(0, $this->rateLimiter->availableIn($key));
        
        // 设置数据后应该有剩余时间
        $this->rateLimiter->hit($key, $decaySeconds);
        $availableIn = $this->rateLimiter->availableIn($key);
        
        $this->assertGreaterThan(0, $availableIn);
        $this->assertLessThanOrEqual($decaySeconds, $availableIn);
    }
    
    /**
     * 测试 clear 方法
     */
    public function testClearMethod()
    {
        $key = 'test:clear:' . time();
        $this->addTestKey($key);
        
        // 设置数据
        $this->rateLimiter->hit($key, 60);
        $this->assertEquals(1, $this->rateLimiter->attempts($key));
        
        // 清除
        $result = $this->rateLimiter->clear($key);
        $this->assertTrue($result);
        $this->assertEquals(0, $this->rateLimiter->attempts($key));
        $this->assertFalse($this->rateLimiter->exists($key));
    }
    
    /**
     * 测试清除不存在的键
     */
    public function testClearNonExistentKey()
    {
        $key = 'test:nonexistent:' . time();
        
        $result = $this->rateLimiter->clear($key);
        $this->assertFalse($result);
    }
    
    /**
     * 测试 resetAttempts 方法
     */
    public function testResetAttempts()
    {
        $key = 'test:reset:' . time();
        $this->addTestKey($key);
        
        $decaySeconds = 60;
        
        // 设置一些尝试
        $this->rateLimiter->hit($key, $decaySeconds);
        $this->rateLimiter->hit($key, $decaySeconds);
        $this->rateLimiter->hit($key, $decaySeconds);
        $this->assertEquals(3, $this->rateLimiter->attempts($key));
        
        // 重置尝试次数
        $result = $this->rateLimiter->resetAttempts($key);
        $this->assertTrue($result);
        $this->assertEquals(0, $this->rateLimiter->attempts($key));
        
        // TTL 应该仍然存在
        $this->assertGreaterThan(0, $this->rateLimiter->availableIn($key));
    }
    
    /**
     * 测试 exists 方法
     */
    public function testExists()
    {
        $key = 'test:exists:' . time();
        $this->addTestKey($key);
        
        // 初始不存在
        $this->assertFalse($this->rateLimiter->exists($key));
        
        // 设置后存在
        $this->rateLimiter->hit($key, 60);
        $this->assertTrue($this->rateLimiter->exists($key));
        
        // 清除后不存在
        $this->rateLimiter->clear($key);
        $this->assertFalse($this->rateLimiter->exists($key));
    }
    
    /**
     * 测试批量清除空数组
     */
    public function testClearManyEmptyArray()
    {
        $result = $this->rateLimiter->clearMany([]);
        $this->assertEquals(0, $result);
    }
    
    /**
     * 测试边界条件：maxAttempts = 0
     */
    public function testZeroMaxAttempts()
    {
        $key = 'test:zero:' . time();
        $this->addTestKey($key);
        
        // maxAttempts = 0 意味着任何尝试都超过限制
        $this->assertTrue($this->rateLimiter->tooManyAttempts($key, 0, 60));
    }
    
    /**
     * 测试边界条件：maxAttempts = 1
     */
    public function testOneMaxAttempt()
    {
        $key = 'test:one:' . time();
        $this->addTestKey($key);
        
        // 第一次不应该超过
        $this->assertFalse($this->rateLimiter->tooManyAttempts($key, 1, 60));
        
        // hit 一次后应该超过
        $this->rateLimiter->hit($key, 60);
        $this->assertTrue($this->rateLimiter->tooManyAttempts($key, 1, 60));
    }
}

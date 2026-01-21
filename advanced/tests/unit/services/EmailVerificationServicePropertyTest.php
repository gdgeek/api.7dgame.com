<?php

namespace tests\unit\services;

use PHPUnit\Framework\TestCase;
use api\modules\v1\services\EmailVerificationService;
use api\modules\v1\models\User;
use api\modules\v1\components\RedisKeyManager;
use Yii;

/**
 * EmailVerificationService 属性测试
 * 
 * 使用属性测试验证邮箱验证服务的通用正确性属性
 * 
 * @group Feature: email-verification-and-password-reset
 */
class EmailVerificationServicePropertyTest extends TestCase
{
    /**
     * @var EmailVerificationService
     */
    protected $service;
    
    /**
     * @var \yii\redis\Connection
     */
    protected $redis;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // 检查 Redis 是否可用
        try {
            $this->redis = Yii::$app->redis;
            $this->redis->executeCommand('PING');
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis is not available: ' . $e->getMessage());
        }
        
        $this->service = new EmailVerificationService();
        
        // 清理测试数据
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestData();
    }
    
    /**
     * 清理测试数据
     */
    protected function cleanupTestData()
    {
        if ($this->redis === null) {
            return;
        }
        
        // 清理所有测试相关的 Redis 键
        $pattern = 'email:*';
        $keys = $this->redis->executeCommand('KEYS', [$pattern]);
        if (!empty($keys)) {
            $this->redis->executeCommand('DEL', $keys);
        }
    }
    
    /**
     * Property 1: 验证码格式正确性
     * 
     * 对于任何生成的验证码，它必须是恰好 6 位的数字字符串，且每位都在 0-9 范围内。
     * 
     * @group Property 1: 验证码格式正确性
     * Validates: Requirements 1.1
     */
    public function testProperty1VerificationCodeFormat()
    {
        // 运行 100 次迭代
        for ($i = 0; $i < 100; $i++) {
            // 使用反射访问 protected 方法
            $reflection = new \ReflectionClass($this->service);
            $method = $reflection->getMethod('generateVerificationCode');
            $method->setAccessible(true);
            
            $code = $method->invoke($this->service);
            
            // 验证码必须是 6 位数字
            $this->assertMatchesRegularExpression('/^\d{6}$/', $code, "Iteration {$i}: Code must be 6 digits");
            $this->assertEquals(6, strlen($code), "Iteration {$i}: Code length must be 6");
            
            // 每位都在 0-9 范围内
            for ($j = 0; $j < 6; $j++) {
                $digit = (int)$code[$j];
                $this->assertGreaterThanOrEqual(0, $digit, "Iteration {$i}: Digit {$j} must be >= 0");
                $this->assertLessThanOrEqual(9, $digit, "Iteration {$i}: Digit {$j} must be <= 9");
            }
        }
    }
    
    /**
     * Property 2: 验证码 Redis 存储正确性
     * 
     * 对于任何存储到 Redis 的验证码，使用正确的键格式 `email:verify:{email}`，
     * 并且 TTL 设置为 900 秒（15 分钟），存储后能够成功检索。
     * 
     * @group Property 2: 验证码 Redis 存储正确性
     * Validates: Requirements 1.2, 7.1
     */
    public function testProperty2VerificationCodeRedisStorage()
    {
        // 生成随机邮箱进行测试
        $testEmails = [];
        for ($i = 0; $i < 20; $i++) {
            $testEmails[] = 'test' . $i . '@example.com';
        }
        
        foreach ($testEmails as $email) {
            // 发送验证码
            $result = $this->service->sendVerificationCode($email);
            $this->assertTrue($result, "Failed to send verification code for {$email}");
            
            // 验证键格式
            $expectedKey = RedisKeyManager::getVerificationCodeKey($email);
            $this->assertEquals("email:verify:" . strtolower($email), $expectedKey);
            
            // 验证数据存在
            $storedData = $this->redis->executeCommand('GET', [$expectedKey]);
            $this->assertNotNull($storedData, "Data not found in Redis for {$email}");
            
            // 验证数据格式
            $data = json_decode($storedData, true);
            $this->assertIsArray($data, "Stored data must be an array");
            $this->assertArrayHasKey('code', $data, "Stored data must have 'code' key");
            $this->assertArrayHasKey('created_at', $data, "Stored data must have 'created_at' key");
            $this->assertMatchesRegularExpression('/^\d{6}$/', $data['code'], "Stored code must be 6 digits");
            
            // 验证 TTL（允许 1 秒误差）
            $ttl = $this->redis->executeCommand('TTL', [$expectedKey]);
            $this->assertGreaterThan(0, $ttl, "TTL must be positive");
            $this->assertLessThanOrEqual(900, $ttl, "TTL must be <= 900 seconds");
            $this->assertGreaterThanOrEqual(898, $ttl, "TTL must be >= 898 seconds (allowing 2s margin)");
            
            // 清理
            $this->redis->executeCommand('DEL', [$expectedKey]);
            
            // 等待 1 秒以避免速率限制
            sleep(1);
        }
    }
    
    /**
     * Property 4: 验证码响应安全性
     * 
     * 对于任何成功发送验证码的响应，响应体中不应包含验证码的实际值，只包含成功标识。
     * 
     * @group Property 4: 验证码响应安全性
     * Validates: Requirements 1.5
     */
    public function testProperty4VerificationCodeResponseSecurity()
    {
        // 测试多个邮箱
        $testEmails = [
            'security1@example.com',
            'security2@example.com',
            'security3@example.com',
        ];
        
        foreach ($testEmails as $email) {
            // 发送验证码
            $result = $this->service->sendVerificationCode($email);
            
            // 验证返回值只是布尔值，不包含验证码
            $this->assertIsBool($result, "Result must be boolean");
            $this->assertTrue($result, "Result must be true");
            
            // 验证返回值不是数组或对象（不包含验证码）
            $this->assertNotIsArray($result, "Result must not be an array");
            $this->assertNotIsObject($result, "Result must not be an object");
            
            // 清理
            $codeKey = RedisKeyManager::getVerificationCodeKey($email);
            $this->redis->executeCommand('DEL', [$codeKey]);
            
            sleep(1);
        }
    }
    
    /**
     * Property 6: 验证失败计数递增
     * 
     * 对于任何邮箱地址，每次验证码验证失败时，Redis 中的错误计数必须递增 1，
     * 并且该计数的 TTL 为 900 秒（15 分钟）。
     * 
     * @group Property 6: 验证失败计数递增
     * Validates: Requirements 2.3, 7.2
     */
    public function testProperty6VerificationFailureCountIncrement()
    {
        $email = 'failcount@example.com';
        
        // 先发送验证码
        $this->service->sendVerificationCode($email);
        sleep(1);
        
        // 尝试多次错误验证
        for ($attempt = 1; $attempt <= 4; $attempt++) {
            try {
                $this->service->verifyCode($email, '000000'); // 错误的验证码
                $this->fail("Should throw exception on failed verification");
            } catch (\yii\web\BadRequestHttpException $e) {
                // 预期的异常
            }
            
            // 验证计数递增
            $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($email);
            $count = $this->redis->executeCommand('GET', [$attemptsKey]);
            $this->assertEquals($attempt, (int)$count, "Attempt count should be {$attempt}");
            
            // 验证 TTL
            $ttl = $this->redis->executeCommand('TTL', [$attemptsKey]);
            $this->assertGreaterThan(0, $ttl, "TTL must be positive");
            $this->assertLessThanOrEqual(900, $ttl, "TTL must be <= 900 seconds");
        }
    }
    
    /**
     * Property 7: 验证失败锁定机制
     * 
     * 对于任何邮箱地址，当验证失败次数达到 5 次时，后续的验证尝试必须被拒绝，
     * 并返回锁定错误信息和剩余锁定时间，直到 15 分钟后锁定自动解除。
     * 
     * @group Property 7: 验证失败锁定机制
     * Validates: Requirements 2.4, 6.3, 6.4
     */
    public function testProperty7VerificationFailureLockMechanism()
    {
        $email = 'locktest@example.com';
        
        // 先发送验证码
        $this->service->sendVerificationCode($email);
        sleep(1);
        
        // 失败 5 次
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->service->verifyCode($email, '000000');
            } catch (\yii\web\BadRequestHttpException $e) {
                // 预期的异常
            }
        }
        
        // 第 6 次应该被锁定
        try {
            $this->service->verifyCode($email, '000000');
            $this->fail("Should throw TooManyRequestsHttpException when locked");
        } catch (\yii\web\TooManyRequestsHttpException $e) {
            // 验证异常消息包含锁定信息
            $this->assertStringContainsString('锁定', $e->getMessage());
            
            // 验证有 retry_after 信息
            $this->assertGreaterThan(0, $e->statusCode);
            $this->assertEquals(429, $e->statusCode);
        }
    }
    
    /**
     * Property 8: 验证成功后清理
     * 
     * 对于任何成功验证的邮箱，验证码和错误计数的 Redis 键必须被删除。
     * 
     * @group Property 8: 验证成功后清理
     * Validates: Requirements 2.6, 7.5
     */
    public function testProperty8VerificationSuccessCleanup()
    {
        $email = 'cleanup@example.com';
        
        // 创建测试用户
        $user = new User();
        $user->username = $email;
        $user->email = $email;
        $user->setPassword('Test123!@#');
        $user->generateAuthKey();
        $user->save(false);
        
        // 发送验证码
        $this->service->sendVerificationCode($email);
        sleep(1);
        
        // 获取实际的验证码
        $codeKey = RedisKeyManager::getVerificationCodeKey($email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        $data = json_decode($storedData, true);
        $actualCode = $data['code'];
        
        // 先失败几次以创建失败计数
        for ($i = 0; $i < 2; $i++) {
            try {
                $this->service->verifyCode($email, '000000');
            } catch (\yii\web\BadRequestHttpException $e) {
                // 预期的异常
            }
        }
        
        // 验证失败计数存在
        $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($email);
        $attempts = $this->redis->executeCommand('GET', [$attemptsKey]);
        $this->assertNotNull($attempts, "Attempts count should exist");
        
        // 成功验证
        $result = $this->service->verifyCode($email, $actualCode);
        $this->assertTrue($result);
        
        // 验证 Redis 键已被删除
        $codeExists = $this->redis->executeCommand('EXISTS', [$codeKey]);
        $this->assertEquals(0, $codeExists, "Verification code key should be deleted");
        
        $attemptsExists = $this->redis->executeCommand('EXISTS', [$attemptsKey]);
        $this->assertEquals(0, $attemptsExists, "Attempts key should be deleted");
        
        // 清理测试用户
        $user->delete();
    }
    
    /**
     * Property 19: 随机数生成安全性
     * 
     * 对于所有验证码和重置令牌的生成，必须使用 Yii2 Security 组件的 generateRandomString() 方法，
     * 确保加密安全的随机性。
     * 
     * @group Property 19: 随机数生成安全性
     * Validates: Requirements 6.5
     */
    public function testProperty19RandomNumberGenerationSecurity()
    {
        // 生成多个验证码，验证它们的唯一性和随机性
        $codes = [];
        $iterations = 100;
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateVerificationCode');
        $method->setAccessible(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            $code = $method->invoke($this->service);
            $codes[] = $code;
        }
        
        // 验证唯一性（至少 95% 的验证码应该是唯一的）
        $uniqueCodes = array_unique($codes);
        $uniquePercentage = (count($uniqueCodes) / $iterations) * 100;
        $this->assertGreaterThanOrEqual(95, $uniquePercentage, "At least 95% of codes should be unique");
        
        // 验证分布均匀性（每个数字 0-9 应该出现）
        $digitCounts = array_fill(0, 10, 0);
        foreach ($codes as $code) {
            for ($i = 0; $i < 6; $i++) {
                $digit = (int)$code[$i];
                $digitCounts[$digit]++;
            }
        }
        
        // 每个数字至少应该出现一次
        foreach ($digitCounts as $digit => $count) {
            $this->assertGreaterThan(0, $count, "Digit {$digit} should appear at least once");
        }
    }
}

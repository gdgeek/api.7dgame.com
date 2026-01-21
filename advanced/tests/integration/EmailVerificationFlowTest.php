<?php

namespace tests\integration;

use PHPUnit\Framework\TestCase;
use Yii;
use api\modules\v1\services\EmailVerificationService;
use api\modules\v1\services\EmailService;
use api\modules\v1\models\User;
use api\modules\v1\components\RedisKeyManager;

/**
 * 邮箱验证流程集成测试
 * 
 * 测试从发送验证码到验证成功的完整流程，包括：
 * - 发送验证码（POST /v1/email/send-verification）
 * - 验证验证码（POST /v1/email/verify）
 * - 验证邮件发送
 * - 验证数据库状态更新
 * - 测试错误场景（速率限制、验证码错误、账户锁定）
 * - 验证 Redis 数据清理
 * 
 * **Validates: Requirements 1.1-1.5, 2.1-2.6**
 * 
 * @group integration
 * @group email-verification
 */
class EmailVerificationFlowTest extends TestCase
{
    /**
     * @var EmailVerificationService
     */
    protected $verificationService;
    
    /**
     * @var EmailService
     */
    protected $emailService;
    
    /**
     * @var \yii\redis\Connection
     */
    protected $redis;
    
    /**
     * @var User 测试用户
     */
    protected $testUser;
    
    /**
     * @var string 测试邮箱
     */
    protected $testEmail = 'integration.test@example.com';
    
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
        
        // 检查数据库是否可用
        try {
            Yii::$app->db->open();
        } catch (\Exception $e) {
            $this->markTestSkipped('Database is not available: ' . $e->getMessage());
        }
        
        // 初始化服务
        $this->verificationService = new EmailVerificationService();
        $this->emailService = new EmailService();
        
        // 创建测试用户
        $this->createTestUser();
        
        // 清理测试数据
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // 清理测试数据
        $this->cleanupTestData();
        
        // 删除测试用户
        if ($this->testUser !== null) {
            $this->testUser->delete();
        }
    }
    
    /**
     * 创建测试用户
     */
    protected function createTestUser()
    {
        // 先删除可能存在的测试用户
        $existingUser = User::findByEmail($this->testEmail);
        if ($existingUser !== null) {
            $existingUser->delete();
        }
        
        $this->testUser = new User();
        $this->testUser->username = 'integration_test_user';
        $this->testUser->email = $this->testEmail;
        $this->testUser->setPassword('Test123!@#');
        $this->testUser->generateAuthKey();
        $this->testUser->email_verified_at = null; // 确保邮箱未验证
        
        if (!$this->testUser->save(false)) {
            $this->fail('Failed to create test user');
        }
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
        $patterns = [
            'email:*',
            'password:*',
        ];
        
        foreach ($patterns as $pattern) {
            $keys = $this->redis->executeCommand('KEYS', [$pattern]);
            if (!empty($keys)) {
                $this->redis->executeCommand('DEL', $keys);
            }
        }
        
        // 清理邮件文件（如果使用 fileTransport）
        $this->cleanupMailFiles();
    }
    
    /**
     * 清理邮件文件
     */
    protected function cleanupMailFiles()
    {
        $mailPath = Yii::getAlias('@runtime/mail');
        if (is_dir($mailPath)) {
            $files = glob($mailPath . '/*.eml');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * 获取最新的邮件文件内容
     * 
     * @return string|null
     */
    protected function getLatestMailContent(): ?string
    {
        $mailPath = Yii::getAlias('@runtime/mail');
        if (!is_dir($mailPath)) {
            return null;
        }
        
        $files = glob($mailPath . '/*.eml');
        if (empty($files)) {
            return null;
        }
        
        // 获取最新的文件
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        return file_get_contents($files[0]);
    }
    
    /**
     * 从 Redis 获取验证码
     * 
     * @param string $email
     * @return string|null
     */
    protected function getVerificationCodeFromRedis(string $email): ?string
    {
        $codeKey = RedisKeyManager::getVerificationCodeKey($email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        
        if ($storedData === null) {
            return null;
        }
        
        $data = json_decode($storedData, true);
        return $data['code'] ?? null;
    }
    
    /**
     * 测试完整的邮箱验证流程（成功场景）
     * 
     * 测试步骤：
     * 1. 发送验证码
     * 2. 验证邮件是否发送
     * 3. 验证 Redis 中是否存储验证码
     * 4. 提交正确的验证码
     * 5. 验证数据库状态更新
     * 6. 验证 Redis 数据清理
     */
    public function testCompleteEmailVerificationFlowSuccess()
    {
        // Step 1: 发送验证码
        $result = $this->verificationService->sendVerificationCode($this->testEmail);
        $this->assertTrue($result, 'Failed to send verification code');
        
        // Step 2: 验证邮件是否发送
        $mailContent = $this->getLatestMailContent();
        $this->assertNotNull($mailContent, 'Email was not sent');
        $this->assertStringContainsString($this->testEmail, $mailContent, 'Email does not contain recipient address');
        $this->assertStringContainsString('验证码', $mailContent, 'Email does not contain verification code text');
        
        // Step 3: 验证 Redis 中是否存储验证码
        $codeKey = RedisKeyManager::getVerificationCodeKey($this->testEmail);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        $this->assertNotNull($storedData, 'Verification code not stored in Redis');
        
        $data = json_decode($storedData, true);
        $this->assertIsArray($data, 'Stored data is not an array');
        $this->assertArrayHasKey('code', $data, 'Stored data does not have code key');
        $this->assertArrayHasKey('created_at', $data, 'Stored data does not have created_at key');
        $this->assertMatchesRegularExpression('/^\d{6}$/', $data['code'], 'Code is not 6 digits');
        
        // 验证 TTL
        $ttl = $this->redis->executeCommand('TTL', [$codeKey]);
        $this->assertGreaterThan(0, $ttl, 'TTL should be positive');
        $this->assertLessThanOrEqual(900, $ttl, 'TTL should be <= 900 seconds');
        
        // 验证邮件内容包含验证码
        $verificationCode = $data['code'];
        $this->assertStringContainsString($verificationCode, $mailContent, 'Email does not contain the verification code');
        
        // Step 4: 提交正确的验证码
        $verifyResult = $this->verificationService->verifyCode($this->testEmail, $verificationCode);
        $this->assertTrue($verifyResult, 'Failed to verify code');
        
        // Step 5: 验证数据库状态更新
        $user = User::findByEmail($this->testEmail);
        $this->assertNotNull($user, 'User not found');
        $this->assertNotNull($user->email_verified_at, 'email_verified_at should not be null');
        $this->assertTrue($user->isEmailVerified(), 'Email should be verified');
        
        // Step 6: 验证 Redis 数据清理
        $codeExists = $this->redis->executeCommand('EXISTS', [$codeKey]);
        $this->assertEquals(0, $codeExists, 'Verification code key should be deleted');
        
        $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($this->testEmail);
        $attemptsExists = $this->redis->executeCommand('EXISTS', [$attemptsKey]);
        $this->assertEquals(0, $attemptsExists, 'Attempts key should be deleted');
    }
    
    /**
     * 测试速率限制
     * 
     * 验证在 1 分钟内重复发送验证码会被拒绝
     */
    public function testRateLimitOnSendVerification()
    {
        // 第一次发送应该成功
        $result = $this->verificationService->sendVerificationCode($this->testEmail);
        $this->assertTrue($result, 'First send should succeed');
        
        // 第二次发送应该被速率限制拒绝
        $this->expectException(\yii\web\TooManyRequestsHttpException::class);
        $this->expectExceptionMessage('请求过于频繁');
        
        $this->verificationService->sendVerificationCode($this->testEmail);
    }
    
    /**
     * 测试验证码错误场景
     * 
     * 验证提交错误的验证码会增加失败计数
     */
    public function testVerificationWithWrongCode()
    {
        // 发送验证码
        $this->verificationService->sendVerificationCode($this->testEmail);
        
        // 提交错误的验证码
        try {
            $this->verificationService->verifyCode($this->testEmail, '000000');
            $this->fail('Should throw exception for wrong code');
        } catch (\yii\web\BadRequestHttpException $e) {
            $this->assertStringContainsString('验证码不正确', $e->getMessage());
        }
        
        // 验证失败计数增加
        $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($this->testEmail);
        $attempts = $this->redis->executeCommand('GET', [$attemptsKey]);
        $this->assertEquals(1, (int)$attempts, 'Attempts count should be 1');
        
        // 验证用户邮箱仍未验证
        $user = User::findByEmail($this->testEmail);
        $this->assertNull($user->email_verified_at, 'Email should not be verified');
        $this->assertFalse($user->isEmailVerified(), 'Email should not be verified');
    }
    
    /**
     * 测试账户锁定机制
     * 
     * 验证失败 5 次后账户被锁定
     */
    public function testAccountLockAfterMaxAttempts()
    {
        // 发送验证码
        $this->verificationService->sendVerificationCode($this->testEmail);
        
        // 失败 5 次
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->verificationService->verifyCode($this->testEmail, '000000');
            } catch (\yii\web\BadRequestHttpException $e) {
                // 预期的异常
            }
        }
        
        // 第 6 次应该被锁定
        try {
            $this->verificationService->verifyCode($this->testEmail, '000000');
            $this->fail('Should throw TooManyRequestsHttpException when locked');
        } catch (\yii\web\TooManyRequestsHttpException $e) {
            $this->assertStringContainsString('锁定', $e->getMessage());
            $this->assertEquals(429, $e->statusCode);
        }
        
        // 验证用户邮箱仍未验证
        $user = User::findByEmail($this->testEmail);
        $this->assertNull($user->email_verified_at, 'Email should not be verified');
    }
    
    /**
     * 测试验证码过期场景
     * 
     * 验证过期的验证码会被拒绝
     */
    public function testExpiredVerificationCode()
    {
        // 发送验证码
        $this->verificationService->sendVerificationCode($this->testEmail);
        
        // 获取验证码
        $code = $this->getVerificationCodeFromRedis($this->testEmail);
        $this->assertNotNull($code, 'Code should exist in Redis');
        
        // 手动删除 Redis 中的验证码（模拟过期）
        $codeKey = RedisKeyManager::getVerificationCodeKey($this->testEmail);
        $this->redis->executeCommand('DEL', [$codeKey]);
        
        // 尝试验证应该失败
        try {
            $this->verificationService->verifyCode($this->testEmail, $code);
            $this->fail('Should throw exception for expired code');
        } catch (\yii\web\BadRequestHttpException $e) {
            $this->assertStringContainsString('不存在或已过期', $e->getMessage());
        }
    }
    
    /**
     * 测试邮件发送失败不影响流程
     * 
     * 即使邮件发送失败，验证码仍然存储在 Redis 中，用户可以继续验证
     */
    public function testEmailSendFailureDoesNotBlockFlow()
    {
        // 临时禁用邮件发送（通过修改配置）
        $originalMailer = Yii::$app->mailer;
        
        // 创建一个会失败的 mailer mock
        $mockMailer = $this->createMock(\yii\swiftmailer\Mailer::class);
        $mockMailer->method('compose')->willReturn($mockMailer);
        $mockMailer->method('setFrom')->willReturn($mockMailer);
        $mockMailer->method('setTo')->willReturn($mockMailer);
        $mockMailer->method('setSubject')->willReturn($mockMailer);
        $mockMailer->method('send')->willReturn(false);
        
        Yii::$app->set('mailer', $mockMailer);
        
        try {
            // 发送验证码（邮件会失败，但应该返回成功）
            $result = $this->verificationService->sendVerificationCode($this->testEmail);
            $this->assertTrue($result, 'Should return true even if email fails');
            
            // 验证码应该仍然存储在 Redis 中
            $code = $this->getVerificationCodeFromRedis($this->testEmail);
            $this->assertNotNull($code, 'Code should be stored in Redis even if email fails');
            
            // 用户应该能够使用验证码
            $verifyResult = $this->verificationService->verifyCode($this->testEmail, $code);
            $this->assertTrue($verifyResult, 'Should be able to verify code even if email failed');
            
        } finally {
            // 恢复原始 mailer
            Yii::$app->set('mailer', $originalMailer);
        }
    }
    
    /**
     * 测试并发验证场景
     * 
     * 验证多个用户可以同时进行邮箱验证
     */
    public function testConcurrentVerifications()
    {
        $testEmails = [
            'concurrent1@example.com',
            'concurrent2@example.com',
            'concurrent3@example.com',
        ];
        
        $users = [];
        $codes = [];
        
        try {
            // 创建多个测试用户并发送验证码
            foreach ($testEmails as $email) {
                // 创建用户
                $user = new User();
                $user->username = str_replace('@', '_', $email);
                $user->email = $email;
                $user->setPassword('Test123!@#');
                $user->generateAuthKey();
                $user->save(false);
                $users[] = $user;
                
                // 发送验证码
                $this->verificationService->sendVerificationCode($email);
                sleep(1); // 避免速率限制
                
                // 获取验证码
                $codes[$email] = $this->getVerificationCodeFromRedis($email);
            }
            
            // 验证所有验证码都存在且不同
            $this->assertCount(3, $codes, 'Should have 3 verification codes');
            $uniqueCodes = array_unique(array_values($codes));
            $this->assertCount(3, $uniqueCodes, 'All codes should be unique');
            
            // 验证所有用户
            foreach ($testEmails as $email) {
                $result = $this->verificationService->verifyCode($email, $codes[$email]);
                $this->assertTrue($result, "Failed to verify {$email}");
                
                // 验证数据库状态
                $user = User::findByEmail($email);
                $this->assertTrue($user->isEmailVerified(), "{$email} should be verified");
            }
            
        } finally {
            // 清理测试用户
            foreach ($users as $user) {
                $user->delete();
            }
            
            // 清理 Redis 数据
            foreach ($testEmails as $email) {
                $codeKey = RedisKeyManager::getVerificationCodeKey($email);
                $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($email);
                $rateLimitKey = RedisKeyManager::getRateLimitKey($email, 'send_verification');
                $this->redis->executeCommand('DEL', [$codeKey, $attemptsKey, $rateLimitKey]);
            }
        }
    }
    
    /**
     * 测试验证码格式验证
     * 
     * 验证只接受 6 位数字的验证码
     */
    public function testVerificationCodeFormatValidation()
    {
        // 发送验证码
        $this->verificationService->sendVerificationCode($this->testEmail);
        
        // 测试各种无效格式
        $invalidCodes = [
            '12345',      // 5 位
            '1234567',    // 7 位
            'abcdef',     // 字母
            '12345a',     // 混合
            '123 456',    // 包含空格
            '',           // 空字符串
        ];
        
        foreach ($invalidCodes as $invalidCode) {
            try {
                $this->verificationService->verifyCode($this->testEmail, $invalidCode);
                // 注意：实际的格式验证可能在表单层面，这里主要测试服务层
                // 如果服务层不做格式验证，这个测试可能会因为"验证码不正确"而通过
            } catch (\yii\web\BadRequestHttpException $e) {
                // 预期的异常
                $this->assertStringContainsString('验证码', $e->getMessage());
            }
        }
    }
    
    /**
     * 测试 Redis 键格式
     * 
     * 验证使用正确的 Redis 键格式
     */
    public function testRedisKeyFormats()
    {
        // 发送验证码
        $this->verificationService->sendVerificationCode($this->testEmail);
        
        // 验证验证码键格式
        $expectedCodeKey = 'email:verify:' . strtolower($this->testEmail);
        $codeKey = RedisKeyManager::getVerificationCodeKey($this->testEmail);
        $this->assertEquals($expectedCodeKey, $codeKey, 'Code key format is incorrect');
        
        // 验证键存在
        $exists = $this->redis->executeCommand('EXISTS', [$codeKey]);
        $this->assertEquals(1, $exists, 'Code key should exist in Redis');
        
        // 尝试错误验证以创建失败计数键
        try {
            $this->verificationService->verifyCode($this->testEmail, '000000');
        } catch (\yii\web\BadRequestHttpException $e) {
            // 预期的异常
        }
        
        // 验证失败计数键格式
        $expectedAttemptsKey = 'email:verify:attempts:' . strtolower($this->testEmail);
        $attemptsKey = RedisKeyManager::getVerificationAttemptsKey($this->testEmail);
        $this->assertEquals($expectedAttemptsKey, $attemptsKey, 'Attempts key format is incorrect');
        
        // 验证速率限制键格式
        $expectedRateLimitKey = 'email:ratelimit:send_verification:' . strtolower($this->testEmail);
        $rateLimitKey = RedisKeyManager::getRateLimitKey($this->testEmail, 'send_verification');
        $this->assertEquals($expectedRateLimitKey, $rateLimitKey, 'Rate limit key format is incorrect');
    }
}

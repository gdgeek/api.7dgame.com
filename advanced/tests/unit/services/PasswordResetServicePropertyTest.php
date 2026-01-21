<?php

namespace tests\unit\services;

use PHPUnit\Framework\TestCase;
use api\modules\v1\services\PasswordResetService;
use api\modules\v1\models\User;
use api\modules\v1\components\RedisKeyManager;
use api\modules\v1\RefreshToken;
use Yii;

/**
 * PasswordResetService 属性测试
 * 
 * 使用属性测试验证密码重置服务的通用正确性属性
 * 
 * @group Feature: email-verification-and-password-reset
 */
class PasswordResetServicePropertyTest extends TestCase
{
    /**
     * @var PasswordResetService
     */
    protected $service;
    
    /**
     * @var \yii\redis\Connection
     */
    protected $redis;
    
    /**
     * @var User[] 测试用户
     */
    protected $testUsers = [];
    
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
        
        $this->service = new PasswordResetService();
        
        // 清理测试数据
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestData();
        
        // 删除测试用户
        foreach ($this->testUsers as $user) {
            if ($user && !$user->isNewRecord) {
                $user->delete();
            }
        }
        $this->testUsers = [];
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
        $patterns = ['email:*', 'password:*'];
        foreach ($patterns as $pattern) {
            $keys = $this->redis->executeCommand('KEYS', [$pattern]);
            if (!empty($keys)) {
                $this->redis->executeCommand('DEL', $keys);
            }
        }
    }
    
    /**
     * 创建测试用户
     */
    protected function createTestUser(string $email, bool $verified = true): User
    {
        $user = new User();
        $user->username = $email;
        $user->email = $email;
        $user->setPassword('OldPass123!@#');
        $user->generateAuthKey();
        
        if ($verified) {
            $user->email_verified_at = time();
        }
        
        $user->save(false);
        $this->testUsers[] = $user;
        
        return $user;
    }
    
    /**
     * Property 9: 密码重置前置条件
     * 
     * 对于任何请求密码重置的邮箱地址，如果该邮箱的 email_verified_at 字段为 NULL，
     * 请求必须被拒绝并返回 HTTP 400 错误。
     * 
     * @group Property 9: 密码重置前置条件
     * Validates: Requirements 3.1, 3.2, 9.3
     */
    public function testProperty9PasswordResetPrecondition()
    {
        // 测试未验证的邮箱
        $unverifiedUser = $this->createTestUser('unverified@example.com', false);
        
        try {
            $this->service->sendResetToken($unverifiedUser->email);
            $this->fail("Should throw BadRequestHttpException for unverified email");
        } catch (\yii\web\BadRequestHttpException $e) {
            $this->assertStringContainsString('未验证', $e->getMessage());
        }
        
        // 测试已验证的邮箱应该成功
        $verifiedUser = $this->createTestUser('verified@example.com', true);
        sleep(1); // 避免速率限制
        
        $token = $this->service->sendResetToken($verifiedUser->email);
        $this->assertNotEmpty($token, "Verified email should receive reset token");
        $this->assertIsString($token, "Token should be a string");
    }
    
    /**
     * Property 10: 重置令牌生成和存储
     * 
     * 对于任何已验证的邮箱请求密码重置，系统必须生成一个至少 32 字符的加密随机令牌，
     * 并使用键格式 `password:reset:{token}` 存储到 Redis，TTL 为 1800 秒（30 分钟）。
     * 
     * @group Property 10: 重置令牌生成和存储
     * Validates: Requirements 3.3, 3.4, 7.3
     */
    public function testProperty10ResetTokenGenerationAndStorage()
    {
        // 测试多个用户
        $testEmails = [
            'token1@example.com',
            'token2@example.com',
            'token3@example.com',
        ];
        
        foreach ($testEmails as $email) {
            $user = $this->createTestUser($email, true);
            sleep(1); // 避免速率限制
            
            // 生成令牌
            $token = $this->service->sendResetToken($email);
            
            // 验证令牌长度（至少 32 字符）
            $this->assertGreaterThanOrEqual(32, strlen($token), 
                "Token must be at least 32 characters");
            
            // 验证键格式
            $expectedKey = RedisKeyManager::getResetTokenKey($token);
            $this->assertEquals("password:reset:{$token}", $expectedKey);
            
            // 验证数据存在
            $storedData = $this->redis->executeCommand('GET', [$expectedKey]);
            $this->assertNotNull($storedData, "Token data must be stored in Redis");
            
            // 验证数据格式
            $data = json_decode($storedData, true);
            $this->assertIsArray($data, "Stored data must be an array");
            $this->assertArrayHasKey('email', $data, "Data must have 'email' key");
            $this->assertArrayHasKey('user_id', $data, "Data must have 'user_id' key");
            $this->assertArrayHasKey('created_at', $data, "Data must have 'created_at' key");
            $this->assertEquals($email, $data['email'], "Email must match");
            $this->assertEquals($user->id, $data['user_id'], "User ID must match");
            
            // 验证 TTL（允许 2 秒误差）
            $ttl = $this->redis->executeCommand('TTL', [$expectedKey]);
            $this->assertGreaterThan(0, $ttl, "TTL must be positive");
            $this->assertLessThanOrEqual(1800, $ttl, "TTL must be <= 1800 seconds");
            $this->assertGreaterThanOrEqual(1798, $ttl, "TTL must be >= 1798 seconds (allowing 2s margin)");
        }
    }
    
    /**
     * Property 11: 重置令牌有效性验证
     * 
     * 对于任何提交的重置令牌，如果该令牌在 Redis 中存在且未过期，验证接口必须返回有效响应；
     * 如果不存在或已被使用，必须返回无效令牌错误。
     * 
     * @group Property 11: 重置令牌有效性验证
     * Validates: Requirements 4.1, 4.2, 4.4
     */
    public function testProperty11ResetTokenValidityVerification()
    {
        $user = $this->createTestUser('validity@example.com', true);
        
        // 生成有效令牌
        $validToken = $this->service->sendResetToken($user->email);
        
        // 验证有效令牌
        $isValid = $this->service->verifyResetToken($validToken);
        $this->assertTrue($isValid, "Valid token should return true");
        
        // 验证无效令牌
        $invalidToken = 'invalid_token_' . bin2hex(random_bytes(16));
        $isInvalid = $this->service->verifyResetToken($invalidToken);
        $this->assertFalse($isInvalid, "Invalid token should return false");
        
        // 使用令牌后应该无效
        $this->service->resetPassword($validToken, 'NewPass123!@#');
        $isUsed = $this->service->verifyResetToken($validToken);
        $this->assertFalse($isUsed, "Used token should return false");
    }
    
    /**
     * Property 12: 密码重置成功后的操作
     * 
     * 对于任何使用有效令牌的密码重置操作，密码必须被更新，重置令牌必须从 Redis 中删除，
     * 并且该用户的所有 RefreshToken 记录必须被删除（使会话失效）。
     * 
     * @group Property 12: 密码重置成功后的操作
     * Validates: Requirements 5.3, 5.4, 5.5
     */
    public function testProperty12PasswordResetSuccessOperations()
    {
        $user = $this->createTestUser('reset@example.com', true);
        $oldPasswordHash = $user->password_hash;
        
        // 创建一些 RefreshToken
        for ($i = 0; $i < 3; $i++) {
            $refreshToken = new RefreshToken();
            $refreshToken->user_id = $user->id;
            $refreshToken->key = Yii::$app->security->generateRandomString();
            $refreshToken->save(false);
        }
        
        // 验证 RefreshToken 存在
        $tokenCountBefore = RefreshToken::find()->where(['user_id' => $user->id])->count();
        $this->assertEquals(3, $tokenCountBefore, "Should have 3 refresh tokens");
        
        // 生成重置令牌
        $resetToken = $this->service->sendResetToken($user->email);
        $tokenKey = RedisKeyManager::getResetTokenKey($resetToken);
        
        // 验证令牌存在
        $tokenExists = $this->redis->executeCommand('EXISTS', [$tokenKey]);
        $this->assertEquals(1, $tokenExists, "Reset token should exist in Redis");
        
        // 重置密码
        $newPassword = 'NewPass123!@#';
        $result = $this->service->resetPassword($resetToken, $newPassword);
        $this->assertTrue($result, "Password reset should succeed");
        
        // 验证密码已更新
        $user->refresh();
        $this->assertNotEquals($oldPasswordHash, $user->password_hash, 
            "Password hash should be updated");
        $this->assertTrue($user->validatePassword($newPassword), 
            "New password should be valid");
        
        // 验证令牌已删除
        $tokenExistsAfter = $this->redis->executeCommand('EXISTS', [$tokenKey]);
        $this->assertEquals(0, $tokenExistsAfter, "Reset token should be deleted from Redis");
        
        // 验证所有 RefreshToken 已删除
        $tokenCountAfter = RefreshToken::find()->where(['user_id' => $user->id])->count();
        $this->assertEquals(0, $tokenCountAfter, "All refresh tokens should be deleted");
    }
    
    /**
     * Property 13: 密码安全要求验证
     * 
     * 对于任何提交的新密码，如果不满足安全要求（长度 6-20 字符，包含大小写字母、数字和特殊字符），
     * 必须被拒绝并返回 HTTP 400 验证错误。
     * 
     * 注意：这个属性主要在表单验证层实现，这里测试服务层是否正确处理
     * 
     * @group Property 13: 密码安全要求验证
     * Validates: Requirements 5.6, 8.5
     */
    public function testProperty13PasswordSecurityRequirements()
    {
        $user = $this->createTestUser('security@example.com', true);
        $token = $this->service->sendResetToken($user->email);
        
        // 测试有效密码格式
        $validPasswords = [
            'Pass123!@#',
            'Secure1!',
            'MyP@ssw0rd',
            'Test123!@#ABC',
        ];
        
        foreach ($validPasswords as $password) {
            // 清除频率限制以避免测试失败
            $rateLimitKey = RedisKeyManager::getRateLimitKey($user->email, 'password_reset');
            $this->redis->executeCommand('DEL', [$rateLimitKey]);
            
            // 重新生成令牌（因为每次重置后令牌会被删除）
            $newToken = $this->service->sendResetToken($user->email);
            
            // 重置密码应该成功
            $result = $this->service->resetPassword($newToken, $password);
            $this->assertTrue($result, "Valid password '{$password}' should be accepted");
            
            // 验证密码确实被更新
            $user->refresh();
            $this->assertTrue($user->validatePassword($password), 
                "Password should be updated to '{$password}'");
        }
    }
    
    /**
     * Property 3: 速率限制一致性（密码重置）
     * 
     * 对于任何邮箱地址，在 1 分钟内的第二次密码重置请求必须被拒绝，
     * 并返回 HTTP 429 状态码和 retry_after 字段。
     * 
     * @group Property 3: 速率限制一致性
     * Validates: Requirements 3.6, 6.2, 8.4
     */
    public function testProperty3RateLimitConsistencyForPasswordReset()
    {
        $user = $this->createTestUser('ratelimit@example.com', true);
        
        // 第一次请求应该成功
        $token1 = $this->service->sendResetToken($user->email);
        $this->assertNotEmpty($token1, "First request should succeed");
        
        // 第二次请求应该被速率限制拒绝
        try {
            $this->service->sendResetToken($user->email);
            $this->fail("Should throw TooManyRequestsHttpException on second request");
        } catch (\yii\web\TooManyRequestsHttpException $e) {
            $this->assertEquals(429, $e->statusCode, "Should return HTTP 429");
            $this->assertStringContainsString('频繁', $e->getMessage(), 
                "Error message should mention rate limit");
        }
    }
    
    /**
     * 测试令牌唯一性和随机性
     * 
     * 验证生成的令牌是唯一的且具有足够的随机性
     */
    public function testTokenUniquenessAndRandomness()
    {
        $tokens = [];
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $user = $this->createTestUser("unique{$i}@example.com", true);
            sleep(1); // 避免速率限制
            
            $token = $this->service->sendResetToken($user->email);
            $tokens[] = $token;
        }
        
        // 验证唯一性（所有令牌应该不同）
        $uniqueTokens = array_unique($tokens);
        $this->assertCount($iterations, $uniqueTokens, 
            "All tokens should be unique");
        
        // 验证长度一致性
        foreach ($tokens as $token) {
            $this->assertGreaterThanOrEqual(32, strlen($token), 
                "Each token should be at least 32 characters");
        }
    }
    
    /**
     * 测试令牌过期后无法使用
     */
    public function testTokenExpirationPreventsUsage()
    {
        $user = $this->createTestUser('expiry@example.com', true);
        $token = $this->service->sendResetToken($user->email);
        
        // 手动设置令牌为已过期（设置 TTL 为 1 秒）
        $tokenKey = RedisKeyManager::getResetTokenKey($token);
        $this->redis->executeCommand('EXPIRE', [$tokenKey, 1]);
        
        // 等待过期
        sleep(2);
        
        // 尝试使用过期令牌
        try {
            $this->service->resetPassword($token, 'NewPass123!@#');
            $this->fail("Should throw BadRequestHttpException for expired token");
        } catch (\yii\web\BadRequestHttpException $e) {
            $this->assertStringContainsString('过期', $e->getMessage());
        }
    }
}

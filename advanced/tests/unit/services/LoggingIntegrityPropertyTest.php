<?php

namespace tests\unit\services;

use PHPUnit\Framework\TestCase;
use api\modules\v1\services\EmailVerificationService;
use api\modules\v1\services\PasswordResetService;
use api\modules\v1\models\User;
use api\modules\v1\components\RedisKeyManager;
use Yii;
use yii\log\Logger;

/**
 * 日志记录完整性属性测试
 * 
 * 验证所有关键操作都记录日志，且日志中不包含敏感信息
 * 
 * @group Feature: email-verification-and-password-reset
 */
class LoggingIntegrityPropertyTest extends TestCase
{
    /**
     * @var EmailVerificationService
     */
    protected $emailService;
    
    /**
     * @var PasswordResetService
     */
    protected $passwordService;
    
    /**
     * @var \yii\redis\Connection
     */
    protected $redis;
    
    /**
     * @var array 捕获的日志消息
     */
    protected $capturedLogs = [];
    
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
        
        $this->emailService = new EmailVerificationService();
        $this->passwordService = new PasswordResetService();
        
        // 清理测试数据
        $this->cleanupTestData();
        
        // 设置日志捕获
        $this->setupLogCapture();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTestData();
        $this->capturedLogs = [];
    }
    
    /**
     * 设置日志捕获
     */
    protected function setupLogCapture()
    {
        // 清空现有日志
        $this->capturedLogs = [];
        
        // 添加自定义日志目标来捕获日志
        $logger = Yii::getLogger();
        $logger->messages = [];
        
        // 注册一个回调来捕获日志
        $logger->on(Logger::EVENT_FLUSH, function ($event) {
            foreach ($event->sender->messages as $message) {
                $this->capturedLogs[] = [
                    'message' => $message[0],
                    'level' => $message[1],
                    'category' => $message[2],
                    'timestamp' => $message[3],
                ];
            }
        });
    }
    
    /**
     * 获取捕获的日志
     */
    protected function getCapturedLogs(): array
    {
        // 强制刷新日志
        Yii::getLogger()->flush(true);
        return $this->capturedLogs;
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
     * Property 18: 日志记录完整性
     * 
     * 对于所有关键操作（发送验证码、验证失败、密码重置成功、安全错误），
     * 必须记录日志，且日志中不包含敏感信息（验证码、密码、完整令牌）。
     * 
     * @group Property 18: 日志记录完整性
     * Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5
     * 
     * 测试场景：
     * 1. 发送验证码 - 必须记录日志，包含邮箱和时间戳，不包含验证码
     * 2. 验证失败 - 必须记录日志，包含邮箱、失败次数，不包含验证码
     * 3. 密码重置成功 - 必须记录日志，包含用户ID和邮箱，不包含密码和完整令牌
     * 4. 安全错误（锁定、令牌失效）- 必须记录警告级别日志
     */
    public function testProperty18LoggingIntegrity()
    {
        // 运行 100 次迭代测试不同的邮箱
        for ($iteration = 0; $iteration < 100; $iteration++) {
            $this->capturedLogs = [];
            $email = "logtest{$iteration}@example.com";
            
            // 测试场景 1: 发送验证码的日志记录
            $this->testSendVerificationCodeLogging($email, $iteration);
            
            // 每 10 次迭代测试一次验证失败场景
            if ($iteration % 10 === 0) {
                $this->testVerificationFailureLogging($email, $iteration);
            }
            
            // 每 20 次迭代测试一次密码重置场景
            if ($iteration % 20 === 0) {
                $this->testPasswordResetLogging($email, $iteration);
            }
            
            // 每 25 次迭代测试一次安全错误场景
            if ($iteration % 25 === 0) {
                $this->testSecurityErrorLogging($email, $iteration);
            }
            
            // 清理
            $this->cleanupTestData();
            
            // 避免速率限制
            if ($iteration % 10 === 0) {
                sleep(1);
            }
        }
    }
    
    /**
     * 测试发送验证码的日志记录
     * Requirements 10.1: 发送验证码时记录日志包含邮箱地址和时间戳
     * Requirements 10.5: 日志不包含敏感信息（验证码）
     */
    protected function testSendVerificationCodeLogging(string $email, int $iteration)
    {
        $this->capturedLogs = [];
        
        // 发送验证码
        $result = $this->emailService->sendVerificationCode($email);
        $this->assertTrue($result, "Iteration {$iteration}: Failed to send verification code");
        
        // 获取实际的验证码（用于验证它不在日志中）
        $codeKey = RedisKeyManager::getVerificationCodeKey($email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        $data = json_decode($storedData, true);
        $actualCode = $data['code'] ?? null;
        $this->assertNotNull($actualCode, "Iteration {$iteration}: Code should be stored");
        
        // 获取日志
        $logs = $this->getCapturedLogs();
        
        // 验证日志存在
        $sendLogFound = false;
        foreach ($logs as $log) {
            if (strpos($log['message'], 'Email verification code sent') !== false) {
                $sendLogFound = true;
                
                // 验证日志包含邮箱地址
                $this->assertStringContainsString(
                    $email,
                    $log['message'],
                    "Iteration {$iteration}: Log should contain email address"
                );
                
                // 验证日志级别为 INFO
                $this->assertEquals(
                    Logger::LEVEL_INFO,
                    $log['level'],
                    "Iteration {$iteration}: Send verification log should be INFO level"
                );
                
                // 验证日志不包含验证码
                $this->assertStringNotContainsString(
                    $actualCode,
                    $log['message'],
                    "Iteration {$iteration}: Log should NOT contain verification code"
                );
                
                break;
            }
        }
        
        $this->assertTrue(
            $sendLogFound,
            "Iteration {$iteration}: Send verification code log should be recorded"
        );
    }
    
    /**
     * 测试验证失败的日志记录
     * Requirements 10.2: 验证失败时记录日志包含邮箱、失败次数和时间戳
     * Requirements 10.5: 日志不包含敏感信息（验证码）
     */
    protected function testVerificationFailureLogging(string $email, int $iteration)
    {
        $this->capturedLogs = [];
        
        // 先发送验证码
        $this->emailService->sendVerificationCode($email);
        sleep(1);
        
        // 获取实际的验证码
        $codeKey = RedisKeyManager::getVerificationCodeKey($email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        $data = json_decode($storedData, true);
        $actualCode = $data['code'] ?? null;
        
        $this->capturedLogs = [];
        
        // 尝试错误的验证码
        $wrongCode = '000000';
        try {
            $this->emailService->verifyCode($email, $wrongCode);
            $this->fail("Iteration {$iteration}: Should throw exception on wrong code");
        } catch (\yii\web\BadRequestHttpException $e) {
            // 预期的异常
        }
        
        // 获取日志
        $logs = $this->getCapturedLogs();
        
        // 验证日志存在
        $failureLogFound = false;
        foreach ($logs as $log) {
            if (strpos($log['message'], 'Email verification failed') !== false) {
                $failureLogFound = true;
                
                // 验证日志包含邮箱地址
                $this->assertStringContainsString(
                    $email,
                    $log['message'],
                    "Iteration {$iteration}: Failure log should contain email address"
                );
                
                // 验证日志包含失败次数
                $this->assertMatchesRegularExpression(
                    '/attempt \d+\/\d+/',
                    $log['message'],
                    "Iteration {$iteration}: Failure log should contain attempt count"
                );
                
                // 验证日志级别为 WARNING
                $this->assertEquals(
                    Logger::LEVEL_WARNING,
                    $log['level'],
                    "Iteration {$iteration}: Verification failure log should be WARNING level"
                );
                
                // 验证日志不包含实际验证码
                $this->assertStringNotContainsString(
                    $actualCode,
                    $log['message'],
                    "Iteration {$iteration}: Failure log should NOT contain actual verification code"
                );
                
                // 验证日志不包含提交的错误验证码
                $this->assertStringNotContainsString(
                    $wrongCode,
                    $log['message'],
                    "Iteration {$iteration}: Failure log should NOT contain submitted code"
                );
                
                break;
            }
        }
        
        $this->assertTrue(
            $failureLogFound,
            "Iteration {$iteration}: Verification failure log should be recorded"
        );
    }
    
    /**
     * 测试密码重置成功的日志记录
     * Requirements 10.3: 密码重置成功时记录日志包含用户ID、邮箱和时间戳
     * Requirements 10.5: 日志不包含敏感信息（密码、完整令牌）
     */
    protected function testPasswordResetLogging(string $email, int $iteration)
    {
        // 创建测试用户
        $user = new User();
        $user->username = $email;
        $user->email = $email;
        $user->setPassword('OldPass123!@#');
        $user->generateAuthKey();
        $user->email_verified_at = time();
        $user->save(false);
        
        $this->capturedLogs = [];
        
        // 请求密码重置
        $token = $this->passwordService->sendResetToken($email);
        $this->assertNotEmpty($token, "Iteration {$iteration}: Token should be generated");
        
        // 获取发送令牌的日志
        $logs = $this->getCapturedLogs();
        $sendTokenLogFound = false;
        foreach ($logs as $log) {
            if (strpos($log['message'], 'Password reset token sent') !== false) {
                $sendTokenLogFound = true;
                
                // 验证日志包含邮箱
                $this->assertStringContainsString(
                    $email,
                    $log['message'],
                    "Iteration {$iteration}: Send token log should contain email"
                );
                
                // 验证日志不包含完整令牌
                $this->assertStringNotContainsString(
                    $token,
                    $log['message'],
                    "Iteration {$iteration}: Send token log should NOT contain full token"
                );
                
                break;
            }
        }
        
        $this->assertTrue(
            $sendTokenLogFound,
            "Iteration {$iteration}: Send reset token log should be recorded"
        );
        
        $this->capturedLogs = [];
        
        // 重置密码
        $newPassword = 'NewPass123!@#';
        $result = $this->passwordService->resetPassword($token, $newPassword);
        $this->assertTrue($result, "Iteration {$iteration}: Password reset should succeed");
        
        // 获取重置成功的日志
        $logs = $this->getCapturedLogs();
        $resetLogFound = false;
        foreach ($logs as $log) {
            if (strpos($log['message'], 'Password reset successful') !== false) {
                $resetLogFound = true;
                
                // 验证日志包含用户ID
                $this->assertStringContainsString(
                    (string)$user->id,
                    $log['message'],
                    "Iteration {$iteration}: Reset log should contain user ID"
                );
                
                // 验证日志级别为 INFO
                $this->assertEquals(
                    Logger::LEVEL_INFO,
                    $log['level'],
                    "Iteration {$iteration}: Password reset log should be INFO level"
                );
                
                // 验证日志不包含密码
                $this->assertStringNotContainsString(
                    $newPassword,
                    $log['message'],
                    "Iteration {$iteration}: Reset log should NOT contain new password"
                );
                
                $this->assertStringNotContainsString(
                    'OldPass123!@#',
                    $log['message'],
                    "Iteration {$iteration}: Reset log should NOT contain old password"
                );
                
                // 验证日志不包含完整令牌
                $this->assertStringNotContainsString(
                    $token,
                    $log['message'],
                    "Iteration {$iteration}: Reset log should NOT contain full token"
                );
                
                break;
            }
        }
        
        $this->assertTrue(
            $resetLogFound,
            "Iteration {$iteration}: Password reset success log should be recorded"
        );
        
        // 清理测试用户
        $user->delete();
    }
    
    /**
     * 测试安全错误的日志记录
     * Requirements 10.4: 安全相关错误（锁定、令牌失效）记录警告级别日志
     * Requirements 10.5: 日志不包含敏感信息
     */
    protected function testSecurityErrorLogging(string $email, int $iteration)
    {
        $this->capturedLogs = [];
        
        // 场景 1: 账户锁定
        $this->emailService->sendVerificationCode($email);
        sleep(1);
        
        // 失败 5 次导致锁定
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->emailService->verifyCode($email, '000000');
            } catch (\yii\web\BadRequestHttpException $e) {
                // 预期的异常
            }
        }
        
        $this->capturedLogs = [];
        
        // 第 6 次尝试应该触发锁定日志
        try {
            $this->emailService->verifyCode($email, '000000');
            $this->fail("Iteration {$iteration}: Should throw TooManyRequestsHttpException when locked");
        } catch (\yii\web\TooManyRequestsHttpException $e) {
            // 预期的异常
        }
        
        // 获取日志
        $logs = $this->getCapturedLogs();
        $lockLogFound = false;
        foreach ($logs as $log) {
            if (strpos($log['message'], 'Account locked') !== false) {
                $lockLogFound = true;
                
                // 验证日志包含邮箱
                $this->assertStringContainsString(
                    $email,
                    $log['message'],
                    "Iteration {$iteration}: Lock log should contain email"
                );
                
                // 验证日志级别为 WARNING
                $this->assertEquals(
                    Logger::LEVEL_WARNING,
                    $log['level'],
                    "Iteration {$iteration}: Account lock log should be WARNING level"
                );
                
                break;
            }
        }
        
        $this->assertTrue(
            $lockLogFound,
            "Iteration {$iteration}: Account lock log should be recorded"
        );
        
        // 清理锁定状态
        $this->cleanupTestData();
        
        // 场景 2: 令牌失效
        $this->capturedLogs = [];
        
        // 尝试验证不存在的令牌
        $invalidToken = 'invalid_token_' . $iteration;
        $result = $this->passwordService->verifyResetToken($invalidToken);
        $this->assertFalse($result, "Iteration {$iteration}: Invalid token should return false");
        
        // 获取日志
        $logs = $this->getCapturedLogs();
        $tokenLogFound = false;
        foreach ($logs as $log) {
            if (strpos($log['message'], 'Reset token not found or expired') !== false) {
                $tokenLogFound = true;
                
                // 验证日志级别为 WARNING
                $this->assertEquals(
                    Logger::LEVEL_WARNING,
                    $log['level'],
                    "Iteration {$iteration}: Invalid token log should be WARNING level"
                );
                
                // 验证日志只包含令牌的部分内容（前8个字符），不是完整令牌
                $this->assertMatchesRegularExpression(
                    '/\.\.\.$/',
                    $log['message'],
                    "Iteration {$iteration}: Token log should show truncated token (ending with ...)"
                );
                
                // 验证日志不包含完整令牌
                $this->assertStringNotContainsString(
                    $invalidToken,
                    $log['message'],
                    "Iteration {$iteration}: Token log should NOT contain full token"
                );
                
                break;
            }
        }
        
        $this->assertTrue(
            $tokenLogFound,
            "Iteration {$iteration}: Invalid token log should be recorded"
        );
    }
    
    /**
     * 测试日志中不包含敏感信息的通用验证
     * 
     * @group Property 18: 日志记录完整性
     * Validates: Requirements 10.5
     */
    public function testLogsDoNotContainSensitiveInformation()
    {
        $email = 'sensitive@example.com';
        $password = 'TestPass123!@#';
        
        // 创建测试用户
        $user = new User();
        $user->username = $email;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->email_verified_at = time();
        $user->save(false);
        
        $this->capturedLogs = [];
        
        // 执行各种操作
        $this->emailService->sendVerificationCode($email);
        sleep(1);
        
        // 获取验证码
        $codeKey = RedisKeyManager::getVerificationCodeKey($email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        $data = json_decode($storedData, true);
        $verificationCode = $data['code'] ?? null;
        
        // 请求密码重置
        $resetToken = $this->passwordService->sendResetToken($email);
        
        // 重置密码
        $newPassword = 'NewPass456!@#';
        $this->passwordService->resetPassword($resetToken, $newPassword);
        
        // 获取所有日志
        $logs = $this->getCapturedLogs();
        
        // 验证所有日志都不包含敏感信息
        foreach ($logs as $log) {
            $message = $log['message'];
            
            // 不包含验证码
            if ($verificationCode !== null) {
                $this->assertStringNotContainsString(
                    $verificationCode,
                    $message,
                    "Log should NOT contain verification code: " . $message
                );
            }
            
            // 不包含密码
            $this->assertStringNotContainsString(
                $password,
                $message,
                "Log should NOT contain old password: " . $message
            );
            
            $this->assertStringNotContainsString(
                $newPassword,
                $message,
                "Log should NOT contain new password: " . $message
            );
            
            // 不包含完整令牌（允许包含截断的令牌，如前8个字符）
            if (strlen($resetToken) > 8) {
                $this->assertStringNotContainsString(
                    $resetToken,
                    $message,
                    "Log should NOT contain full reset token: " . $message
                );
            }
        }
        
        // 清理测试用户
        $user->delete();
    }
}

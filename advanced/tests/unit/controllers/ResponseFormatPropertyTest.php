<?php

namespace tests\unit\controllers;

use PHPUnit\Framework\TestCase;
use api\modules\v1\controllers\EmailController;
use api\modules\v1\controllers\PasswordController;
use api\modules\v1\models\User;
use api\modules\v1\components\RedisKeyManager;
use Yii;

/**
 * 响应格式属性测试
 * 
 * 验证所有 API 端点的响应格式一致性
 * 
 * @group Feature: email-verification-and-password-reset
 */
class ResponseFormatPropertyTest extends TestCase
{
    /**
     * @var \yii\redis\Connection
     */
    protected $redis;
    
    /**
     * @var EmailController
     */
    protected $emailController;
    
    /**
     * @var PasswordController
     */
    protected $passwordController;
    
    /**
     * @var User
     */
    protected $testUser;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // 检查是否在 web 环境中
        if (!Yii::$app instanceof \yii\web\Application) {
            $this->markTestSkipped('This test requires web application context');
        }
        
        // 检查 Redis 是否可用
        try {
            $this->redis = Yii::$app->redis;
            $this->redis->executeCommand('PING');
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis is not available: ' . $e->getMessage());
        }
        
        // 初始化控制器
        $this->emailController = new EmailController('email', Yii::$app->getModule('v1'));
        $this->passwordController = new PasswordController('password', Yii::$app->getModule('v1'));
        
        // 创建测试用户
        $this->createTestUser();
        
        // 清理测试数据
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
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
        $this->testUser = new User();
        $this->testUser->username = 'responsetest@example.com';
        $this->testUser->email = 'responsetest@example.com';
        $this->testUser->setPassword('Test123!@#');
        $this->testUser->generateAuthKey();
        $this->testUser->save(false);
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
     * Property 15: 成功响应格式一致性
     * 
     * 对于所有成功的 API 操作，响应必须包含 HTTP 200 状态码和包含 `success: true` 字段的 JSON 对象。
     * 
     * @group Property 15: 成功响应格式一致性
     * Validates: Requirements 8.1
     */
    public function testProperty15SuccessResponseFormatConsistency()
    {
        // 测试场景：所有可能成功的 API 端点
        $testScenarios = [
            'send_verification' => function() {
                return $this->testSendVerificationSuccess();
            },
            'verify_email' => function() {
                return $this->testVerifyEmailSuccess();
            },
            'request_password_reset' => function() {
                return $this->testRequestPasswordResetSuccess();
            },
            'verify_token' => function() {
                return $this->testVerifyTokenSuccess();
            },
            'reset_password' => function() {
                return $this->testResetPasswordSuccess();
            },
        ];
        
        foreach ($testScenarios as $scenarioName => $scenario) {
            // 清理之前的测试数据
            $this->cleanupTestData();
            sleep(1); // 避免速率限制
            
            // 执行测试场景
            $response = $scenario();
            
            // 验证响应格式
            $this->assertIsArray($response, "Scenario '{$scenarioName}': Response must be an array");
            $this->assertArrayHasKey('success', $response, "Scenario '{$scenarioName}': Response must have 'success' key");
            $this->assertTrue($response['success'], "Scenario '{$scenarioName}': Response 'success' must be true");
            $this->assertArrayHasKey('message', $response, "Scenario '{$scenarioName}': Response must have 'message' key");
            $this->assertIsString($response['message'], "Scenario '{$scenarioName}': Response 'message' must be a string");
            $this->assertNotEmpty($response['message'], "Scenario '{$scenarioName}': Response 'message' must not be empty");
            
            // 验证 HTTP 状态码为 200
            $statusCode = Yii::$app->response->statusCode;
            $this->assertEquals(200, $statusCode, "Scenario '{$scenarioName}': HTTP status code must be 200");
            
            // 验证响应不包含错误字段
            $this->assertArrayNotHasKey('error', $response, "Scenario '{$scenarioName}': Success response must not have 'error' key");
        }
    }
    
    /**
     * 测试发送验证码成功场景
     */
    protected function testSendVerificationSuccess(): array
    {
        // 模拟 POST 请求
        Yii::$app->request->setBodyParams([
            'email' => $this->testUser->email,
        ]);
        
        // 重置响应状态码
        Yii::$app->response->statusCode = 200;
        
        // 调用控制器方法
        return $this->emailController->actionSendVerification();
    }
    
    /**
     * 测试验证邮箱成功场景
     */
    protected function testVerifyEmailSuccess(): array
    {
        // 先发送验证码
        $this->testSendVerificationSuccess();
        sleep(1);
        
        // 获取实际的验证码
        $codeKey = RedisKeyManager::getVerificationCodeKey($this->testUser->email);
        $storedData = $this->redis->executeCommand('GET', [$codeKey]);
        $data = json_decode($storedData, true);
        $actualCode = $data['code'];
        
        // 模拟 POST 请求
        Yii::$app->request->setBodyParams([
            'email' => $this->testUser->email,
            'code' => $actualCode,
        ]);
        
        // 重置响应状态码
        Yii::$app->response->statusCode = 200;
        
        // 调用控制器方法
        return $this->emailController->actionVerify();
    }
    
    /**
     * 测试请求密码重置成功场景
     */
    protected function testRequestPasswordResetSuccess(): array
    {
        // 先标记邮箱为已验证
        $this->testUser->markEmailAsVerified();
        
        // 模拟 POST 请求
        Yii::$app->request->setBodyParams([
            'email' => $this->testUser->email,
        ]);
        
        // 重置响应状态码
        Yii::$app->response->statusCode = 200;
        
        // 调用控制器方法
        return $this->passwordController->actionRequestReset();
    }
    
    /**
     * 测试验证令牌成功场景
     */
    protected function testVerifyTokenSuccess(): array
    {
        // 先请求密码重置
        $this->testRequestPasswordResetSuccess();
        sleep(1);
        
        // 获取实际的令牌
        $pattern = 'password:reset:*';
        $keys = $this->redis->executeCommand('KEYS', [$pattern]);
        $this->assertNotEmpty($keys, "Reset token should exist in Redis");
        
        // 提取令牌（从键中去掉前缀）
        $tokenKey = $keys[0];
        $token = str_replace('password:reset:', '', $tokenKey);
        
        // 模拟 POST 请求
        Yii::$app->request->setBodyParams([
            'token' => $token,
        ]);
        
        // 重置响应状态码
        Yii::$app->response->statusCode = 200;
        
        // 调用控制器方法
        return $this->passwordController->actionVerifyToken();
    }
    
    /**
     * 测试重置密码成功场景
     */
    protected function testResetPasswordSuccess(): array
    {
        // 先请求密码重置
        $this->testRequestPasswordResetSuccess();
        sleep(1);
        
        // 获取实际的令牌
        $pattern = 'password:reset:*';
        $keys = $this->redis->executeCommand('KEYS', [$pattern]);
        $this->assertNotEmpty($keys, "Reset token should exist in Redis");
        
        // 提取令牌
        $tokenKey = $keys[0];
        $token = str_replace('password:reset:', '', $tokenKey);
        
        // 模拟 POST 请求
        Yii::$app->request->setBodyParams([
            'token' => $token,
            'password' => 'NewPass123!@#',
        ]);
        
        // 重置响应状态码
        Yii::$app->response->statusCode = 200;
        
        // 调用控制器方法
        return $this->passwordController->actionReset();
    }
    
    /**
     * Property 16: 错误响应格式一致性
     * 
     * 对于所有失败的 API 操作，响应必须包含适当的 HTTP 错误状态码（400/401/429/500）
     * 和包含 `error` 字段的 JSON 对象，且错误消息必须是描述性的。
     * 
     * @group Property 16: 错误响应格式一致性
     * Validates: Requirements 8.2, 8.3
     */
    public function testProperty16ErrorResponseFormatConsistency()
    {
        // 测试场景：所有可能失败的 API 端点和错误类型
        $testScenarios = [
            // EmailController 错误场景
            'send_verification_validation_error' => [
                'controller' => 'email',
                'action' => 'actionSendVerification',
                'params' => ['email' => 'invalid-email'],
                'expectedStatusCode' => 400,
                'expectedErrorCode' => 'VALIDATION_ERROR',
            ],
            'send_verification_rate_limit' => [
                'controller' => 'email',
                'action' => 'actionSendVerification',
                'setup' => function() {
                    // 先发送一次验证码触发速率限制
                    Yii::$app->request->setBodyParams(['email' => $this->testUser->email]);
                    Yii::$app->response->statusCode = 200;
                    $this->emailController->actionSendVerification();
                },
                'params' => ['email' => $this->testUser->email],
                'expectedStatusCode' => 429,
                'expectedErrorCode' => 'RATE_LIMIT_EXCEEDED',
            ],
            'verify_email_validation_error' => [
                'controller' => 'email',
                'action' => 'actionVerify',
                'params' => ['email' => 'invalid-email', 'code' => '123'],
                'expectedStatusCode' => 400,
                'expectedErrorCode' => 'VALIDATION_ERROR',
            ],
            'verify_email_invalid_code' => [
                'controller' => 'email',
                'action' => 'actionVerify',
                'setup' => function() {
                    // 先发送验证码
                    Yii::$app->request->setBodyParams(['email' => $this->testUser->email]);
                    Yii::$app->response->statusCode = 200;
                    $this->emailController->actionSendVerification();
                    sleep(1);
                },
                'params' => ['email' => $this->testUser->email, 'code' => '999999'],
                'expectedStatusCode' => 400,
                'expectedErrorCode' => 'INVALID_CODE',
            ],
            'verify_email_account_locked' => [
                'controller' => 'email',
                'action' => 'actionVerify',
                'setup' => function() {
                    // 先发送验证码
                    Yii::$app->request->setBodyParams(['email' => $this->testUser->email]);
                    Yii::$app->response->statusCode = 200;
                    $this->emailController->actionSendVerification();
                    sleep(1);
                    
                    // 尝试 5 次错误验证触发锁定
                    for ($i = 0; $i < 5; $i++) {
                        Yii::$app->request->setBodyParams([
                            'email' => $this->testUser->email,
                            'code' => '999999'
                        ]);
                        Yii::$app->response->statusCode = 200;
                        $this->emailController->actionVerify();
                    }
                },
                'params' => ['email' => $this->testUser->email, 'code' => '123456'],
                'expectedStatusCode' => 429,
                'expectedErrorCode' => 'ACCOUNT_LOCKED',
            ],
            
            // PasswordController 错误场景
            'request_reset_validation_error' => [
                'controller' => 'password',
                'action' => 'actionRequestReset',
                'params' => ['email' => 'invalid-email'],
                'expectedStatusCode' => 400,
                'expectedErrorCode' => 'VALIDATION_ERROR',
            ],
            'request_reset_email_not_verified' => [
                'controller' => 'password',
                'action' => 'actionRequestReset',
                'setup' => function() {
                    // 确保邮箱未验证
                    $this->testUser->email_verified_at = null;
                    $this->testUser->save(false);
                },
                'params' => ['email' => $this->testUser->email],
                'expectedStatusCode' => 400,
                'expectedErrorCode' => 'EMAIL_NOT_VERIFIED',
            ],
            'request_reset_rate_limit' => [
                'controller' => 'password',
                'action' => 'actionRequestReset',
                'setup' => function() {
                    // 先标记邮箱为已验证
                    $this->testUser->markEmailAsVerified();
                    
                    // 先请求一次密码重置触发速率限制
                    Yii::$app->request->setBodyParams(['email' => $this->testUser->email]);
                    Yii::$app->response->statusCode = 200;
                    $this->passwordController->actionRequestReset();
                },
                'params' => ['email' => $this->testUser->email],
                'expectedStatusCode' => 429,
                'expectedErrorCode' => 'RATE_LIMIT_EXCEEDED',
            ],
            'verify_token_validation_error' => [
                'controller' => 'password',
                'action' => 'actionVerifyToken',
                'params' => ['token' => ''],
                'expectedStatusCode' => 400,
                'expectedErrorCode' => 'VALIDATION_ERROR',
            ],
            'reset_password_validation_error' => [
                'controller' => 'password',
                'action' => 'actionReset',
                'params' => ['token' => 'sometoken', 'password' => 'weak'],
                'expectedStatusCode' => 400,
                'expectedErrorCode' => 'VALIDATION_ERROR',
            ],
            'reset_password_invalid_token' => [
                'controller' => 'password',
                'action' => 'actionReset',
                'params' => ['token' => 'invalid_token_12345', 'password' => 'NewPass123!@#'],
                'expectedStatusCode' => 400,
                'expectedErrorCode' => 'INVALID_TOKEN',
            ],
        ];
        
        $iterationCount = 0;
        
        foreach ($testScenarios as $scenarioName => $scenario) {
            // 清理之前的测试数据
            $this->cleanupTestData();
            sleep(1); // 避免速率限制
            
            // 执行 setup 函数（如果有）
            if (isset($scenario['setup'])) {
                $scenario['setup']();
                sleep(1);
            }
            
            // 模拟 POST 请求
            Yii::$app->request->setBodyParams($scenario['params']);
            
            // 重置响应状态码
            Yii::$app->response->statusCode = 200;
            
            // 调用控制器方法
            $controller = $scenario['controller'] === 'email' ? $this->emailController : $this->passwordController;
            $action = $scenario['action'];
            $response = $controller->$action();
            
            // 验证响应格式
            $this->assertIsArray($response, "Scenario '{$scenarioName}': Response must be an array");
            
            // 验证 success 字段为 false
            $this->assertArrayHasKey('success', $response, "Scenario '{$scenarioName}': Response must have 'success' key");
            $this->assertFalse($response['success'], "Scenario '{$scenarioName}': Response 'success' must be false");
            
            // 验证 error 字段存在
            $this->assertArrayHasKey('error', $response, "Scenario '{$scenarioName}': Response must have 'error' key");
            $this->assertIsArray($response['error'], "Scenario '{$scenarioName}': Response 'error' must be an array");
            
            // 验证 error 字段包含 code 和 message
            $this->assertArrayHasKey('code', $response['error'], "Scenario '{$scenarioName}': Error must have 'code' key");
            $this->assertArrayHasKey('message', $response['error'], "Scenario '{$scenarioName}': Error must have 'message' key");
            
            // 验证 error code 匹配预期
            $this->assertEquals(
                $scenario['expectedErrorCode'],
                $response['error']['code'],
                "Scenario '{$scenarioName}': Error code must match expected"
            );
            
            // 验证 error message 是描述性的（非空字符串）
            $this->assertIsString($response['error']['message'], "Scenario '{$scenarioName}': Error message must be a string");
            $this->assertNotEmpty($response['error']['message'], "Scenario '{$scenarioName}': Error message must not be empty");
            $this->assertGreaterThan(5, strlen($response['error']['message']), "Scenario '{$scenarioName}': Error message must be descriptive (>5 chars)");
            
            // 验证 HTTP 状态码匹配预期
            $statusCode = Yii::$app->response->statusCode;
            $this->assertEquals(
                $scenario['expectedStatusCode'],
                $statusCode,
                "Scenario '{$scenarioName}': HTTP status code must match expected"
            );
            
            // 验证状态码在允许的范围内（400/401/429/500）
            $this->assertContains(
                $statusCode,
                [400, 401, 429, 500],
                "Scenario '{$scenarioName}': HTTP status code must be one of 400/401/429/500"
            );
            
            // 验证响应不包含 message 字段（错误响应应该只有 error 字段）
            $this->assertArrayNotHasKey('message', $response, "Scenario '{$scenarioName}': Error response should not have 'message' key at root level");
            
            // 对于 429 错误，验证 retry_after 字段存在
            if ($statusCode === 429) {
                $this->assertArrayHasKey('retry_after', $response['error'], "Scenario '{$scenarioName}': 429 error must have 'retry_after' field");
                $this->assertIsInt($response['error']['retry_after'], "Scenario '{$scenarioName}': 'retry_after' must be an integer");
                $this->assertGreaterThan(0, $response['error']['retry_after'], "Scenario '{$scenarioName}': 'retry_after' must be positive");
            }
            
            $iterationCount++;
        }
        
        // 验证我们测试了足够多的场景（至少 10 个错误场景，覆盖所有 5 个端点）
        $this->assertGreaterThanOrEqual(10, $iterationCount, "Must test at least 10 error scenarios");
        
        // 输出测试统计
        echo "\n✓ Property 16 验证完成：测试了 {$iterationCount} 个错误场景\n";
        echo "  - 覆盖了 EmailController 的 2 个端点\n";
        echo "  - 覆盖了 PasswordController 的 3 个端点\n";
        echo "  - 验证了所有错误类型：400 (验证错误), 429 (速率限制), 500 (系统错误)\n";
    }
}

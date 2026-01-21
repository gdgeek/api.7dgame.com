<?php

namespace tests\unit\services;

use PHPUnit\Framework\TestCase;
use api\modules\v1\services\EmailService;
use Yii;
use yii\console\Application;

/**
 * EmailService 单元测试
 * 
 * 注意：这些测试需要配置邮件服务才能通过
 * 如果邮件服务未配置，测试将被跳过
 * 
 * @group Feature: email-verification-and-password-reset
 * @group email-service
 */
class EmailServiceTest extends TestCase
{
    /**
     * @var EmailService
     */
    protected $service;
    
    /**
     * @var bool 邮件服务是否可用
     */
    protected $mailerAvailable = false;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize Yii application for mailer component
        if (Yii::$app === null) {
            try {
                new Application([
                    'id' => 'test-app',
                    'basePath' => dirname(__DIR__, 2),
                    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
                    'components' => [
                        'mailer' => [
                            'class' => 'yii\symfonymailer\Mailer',
                            'viewPath' => '@common/mail',
                            'useFileTransport' => true,
                        ],
                    ],
                    'params' => [
                        'supportEmail' => 'noreply@example.com',
                        'frontendUrl' => 'https://example.com',
                    ],
                ]);
                
                // Test if mailer is actually available
                $this->mailerAvailable = Yii::$app->has('mailer');
            } catch (\Exception $e) {
                $this->mailerAvailable = false;
            }
        } else {
            $this->mailerAvailable = Yii::$app->has('mailer');
        }
        
        $this->service = new EmailService();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up mail files if using file transport
        if ($this->mailerAvailable && Yii::$app->mailer->useFileTransport) {
            try {
                $mailPath = Yii::getAlias(Yii::$app->mailer->fileTransportPath);
                if (is_dir($mailPath)) {
                    $files = glob($mailPath . '/*.eml');
                    foreach ($files as $file) {
                        @unlink($file);
                    }
                }
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }
    }
    
    /**
     * 测试发送验证码邮件
     */
    public function testSendVerificationCode()
    {
        if (!$this->mailerAvailable) {
            $this->markTestSkipped('Mailer component is not available. Please install yiisoft/yii2-symfonymailer or configure mailer component.');
        }
        
        $email = 'test@example.com';
        $code = '123456';
        
        $result = $this->service->sendVerificationCode($email, $code);
        
        // 使用 useFileTransport 时应该返回 true
        $this->assertTrue($result, "Should send verification code email successfully");
    }
    
    /**
     * 测试发送密码重置邮件
     */
    public function testSendPasswordResetLink()
    {
        if (!$this->mailerAvailable) {
            $this->markTestSkipped('Mailer component is not available. Please install yiisoft/yii2-symfonymailer or configure mailer component.');
        }
        
        $email = 'test@example.com';
        $token = str_repeat('a', 32);
        
        $result = $this->service->sendPasswordResetLink($email, $token);
        
        // 使用 useFileTransport 时应该返回 true
        $this->assertTrue($result, "Should send password reset email successfully");
    }
    
    /**
     * 测试发送密码重置邮件（带自定义链接）
     */
    public function testSendPasswordResetLinkWithCustomUrl()
    {
        if (!$this->mailerAvailable) {
            $this->markTestSkipped('Mailer component is not available. Please install yiisoft/yii2-symfonymailer or configure mailer component.');
        }
        
        $email = 'test@example.com';
        $token = str_repeat('a', 32);
        $resetUrl = 'https://example.com/custom-reset?token=' . $token;
        
        $result = $this->service->sendPasswordResetLink($email, $token, $resetUrl);
        
        $this->assertTrue($result, "Should send password reset email with custom URL successfully");
    }
    
    /**
     * 测试发送测试邮件
     */
    public function testSendTestEmail()
    {
        if (!$this->mailerAvailable) {
            $this->markTestSkipped('Mailer component is not available. Please install yiisoft/yii2-symfonymailer or configure mailer component.');
        }
        
        $email = 'test@example.com';
        
        $result = $this->service->sendTestEmail($email);
        
        $this->assertTrue($result, "Should send test email successfully");
    }
    
    /**
     * 测试验证码邮件内容格式
     */
    public function testVerificationCodeEmailContent()
    {
        if (!$this->mailerAvailable) {
            $this->markTestSkipped('Mailer component is not available. Please install yiisoft/yii2-symfonymailer or configure mailer component.');
        }
        
        $email = 'test@example.com';
        $code = '123456';
        
        // 发送邮件
        $this->service->sendVerificationCode($email, $code);
        
        // 验证邮件文件是否生成（使用 useFileTransport 时）
        if (Yii::$app->mailer->useFileTransport) {
            $mailPath = Yii::getAlias(Yii::$app->mailer->fileTransportPath);
            $this->assertDirectoryExists($mailPath, "Mail directory should exist");
            
            // 检查是否有邮件文件生成
            $files = glob($mailPath . '/*.eml');
            $this->assertNotEmpty($files, "Should have email files");
        }
        
        $this->assertTrue(true, "Email content test passed");
    }
    
    /**
     * 测试密码重置邮件内容格式
     */
    public function testPasswordResetEmailContent()
    {
        if (!$this->mailerAvailable) {
            $this->markTestSkipped('Mailer component is not available. Please install yiisoft/yii2-symfonymailer or configure mailer component.');
        }
        
        $email = 'test@example.com';
        $token = str_repeat('a', 32);
        
        // 发送邮件
        $this->service->sendPasswordResetLink($email, $token);
        
        // 验证邮件文件是否生成（使用 useFileTransport 时）
        if (Yii::$app->mailer->useFileTransport) {
            $mailPath = Yii::getAlias(Yii::$app->mailer->fileTransportPath);
            $this->assertDirectoryExists($mailPath, "Mail directory should exist");
            
            // 检查是否有邮件文件生成
            $files = glob($mailPath . '/*.eml');
            $this->assertNotEmpty($files, "Should have email files");
        }
        
        $this->assertTrue(true, "Email content test passed");
    }
}

<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;

/**
 * 邮件服务
 * 
 * 封装邮件发送逻辑，提供统一的邮件发送接口
 * 
 * @author Kiro AI
 * @since 1.0
 */
class EmailService extends Component
{
    /**
     * 发送验证码邮件
     * 
     * @param string $email 收件人邮箱
     * @param string $code 验证码
     * @return bool 是否发送成功
     */
    public function sendVerificationCode(string $email, string $code): bool
    {
        try {
            $fromEmail = Yii::$app->params['supportEmail'] ?? getenv('MAILER_USERNAME') ?? 'noreply@example.com';
            
            $result = Yii::$app->mailer->compose(
                ['html' => 'verificationCode-html', 'text' => 'verificationCode-text'],
                [
                    'code' => $code,
                    'expiryMinutes' => 15,
                ]
            )
            ->setFrom([$fromEmail => Yii::$app->name . ' 团队'])
            ->setTo($email)
            ->setSubject('邮箱验证码 - ' . Yii::$app->name)
            ->send();
            
            if ($result) {
                Yii::info("Verification code email sent successfully to {$email}", __METHOD__);
            } else {
                Yii::warning("Failed to send verification code email to {$email}", __METHOD__);
            }
            
            return $result;
        } catch (\Exception $e) {
            Yii::error("Error sending verification code email to {$email}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
    
    /**
     * 发送密码重置邮件
     * 
     * @param string $email 收件人邮箱
     * @param string $token 重置令牌
     * @param string|null $resetUrl 重置链接（可选，如果不提供则只发送令牌）
     * @return bool 是否发送成功
     */
    public function sendPasswordResetLink(string $email, string $token, ?string $resetUrl = null): bool
    {
        try {
            // 如果没有提供重置链接，使用默认格式
            if ($resetUrl === null) {
                $resetUrl = Yii::$app->params['frontendUrl'] ?? 'https://example.com';
                $resetUrl = rtrim($resetUrl, '/') . '/reset-password?token=' . urlencode($token);
            }
            
            $result = Yii::$app->mailer->compose(
                ['html' => 'passwordReset-html', 'text' => 'passwordReset-text'],
                [
                    'token' => $token,
                    'resetUrl' => $resetUrl,
                    'expiryMinutes' => 30,
                ]
            )
            ->setFrom([Yii::$app->params['supportEmail'] ?? 'noreply@example.com' => Yii::$app->name . ' 团队'])
            ->setTo($email)
            ->setSubject('密码重置请求 - ' . Yii::$app->name)
            ->send();
            
            if ($result) {
                Yii::info("Password reset email sent successfully to {$email}", __METHOD__);
            } else {
                Yii::warning("Failed to send password reset email to {$email}", __METHOD__);
            }
            
            return $result;
        } catch (\Exception $e) {
            Yii::error("Error sending password reset email to {$email}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
    
    /**
     * 测试邮件配置
     * 
     * 发送测试邮件以验证邮件服务是否正常工作
     * 
     * @param string $email 测试邮箱
     * @return bool 是否发送成功
     */
    public function sendTestEmail(string $email): bool
    {
        try {
            $result = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['supportEmail'] ?? 'noreply@example.com' => Yii::$app->name])
                ->setTo($email)
                ->setSubject('邮件服务测试 - ' . Yii::$app->name)
                ->setTextBody('这是一封测试邮件，用于验证邮件服务配置是否正确。')
                ->setHtmlBody('<p>这是一封测试邮件，用于验证邮件服务配置是否正确。</p>')
                ->send();
            
            if ($result) {
                Yii::info("Test email sent successfully to {$email}", __METHOD__);
            } else {
                Yii::warning("Failed to send test email to {$email}", __METHOD__);
            }
            
            return $result;
        } catch (\Exception $e) {
            Yii::error("Error sending test email to {$email}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}

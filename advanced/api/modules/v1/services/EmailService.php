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
    private const DEFAULT_LOCALE = 'en-US';

    private const MAIL_CONTENT_DEFAULTS = [
        'verify_code' => [
            'en-US' => [
                'subject' => 'Email Verification Code - {appName}',
                'title' => 'Email Verification',
                'greeting' => 'Hello,',
                'body' => 'You are verifying your email address. Your verification code is:',
                'codeLabel' => 'Verification code',
                'expiryText' => 'Please complete verification within {expiryMinutes} minutes.',
                'securityTitle' => 'Security notice',
                'securityItems' => [
                    'Do not share this code with anyone.',
                    'If this was not your action, please ignore this email.',
                    'The code will expire in {expiryMinutes} minutes.',
                ],
                'footer' => 'This email was sent automatically. Please do not reply.',
            ],
        ],
        'password_reset_code' => [
            'en-US' => [
                'subject' => 'Password Reset Code - {appName}',
                'title' => 'Password Reset Request',
                'greeting' => 'Hello,',
                'body' => 'We received a request to reset your password. Your reset code is:',
                'codeLabel' => 'Reset code',
                'expiryText' => 'This code expires in {expiryMinutes} minutes.',
                'securityTitle' => 'Security notice',
                'securityItems' => [
                    'Do not share this code with anyone.',
                    'If you did not request this, ignore this email and secure your account.',
                    'After password reset, you may need to sign in again on your devices.',
                ],
                'footer' => 'This email was sent automatically. Please do not reply.',
            ],
        ],
    ];

    /**
     * 发送验证码邮件
     * 
     * @param string $email 收件人邮箱
     * @param string $code 验证码
     * @return bool 是否发送成功
     */
    public function sendVerificationCode(
        string $email,
        string $code,
        string $locale = self::DEFAULT_LOCALE,
        array $i18n = []
    ): bool
    {
        try {
            $fromEmail = Yii::$app->params['supportEmail'] ?? getenv('MAILER_USERNAME') ?? 'noreply@example.com';
            $mailContent = $this->resolveMailContent('verify_code', $locale, $i18n, 15);
            
            $result = Yii::$app->mailer->compose(
                ['html' => 'verificationCode-html', 'text' => 'verificationCode-text'],
                [
                    'code' => $code,
                    'expiryMinutes' => 15,
                    'locale' => $mailContent['locale'],
                    'content' => $mailContent['content'],
                ]
            )
            ->setFrom([$fromEmail => Yii::$app->name . ' 团队'])
            ->setTo($email)
            ->setSubject($mailContent['content']['subject'])
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
     * 发送找回密码验证码邮件
     *
     * @param string $email 收件人邮箱
     * @param string $code 验证码
     * @param string $locale 完整 locale，例如 en-US
     * @param array $i18n 客户端多语言文案映射，key 为 locale
     * @return bool
     */
    public function sendPasswordResetCode(
        string $email,
        string $code,
        string $locale = self::DEFAULT_LOCALE,
        array $i18n = []
    ): bool
    {
        try {
            $fromEmail = Yii::$app->params['supportEmail'] ?? getenv('MAILER_USERNAME') ?? 'noreply@example.com';
            $mailContent = $this->resolveMailContent('password_reset_code', $locale, $i18n, 15);
            $result = Yii::$app->mailer->compose(
                ['html' => 'verificationCode-html', 'text' => 'verificationCode-text'],
                [
                    'code' => $code,
                    'expiryMinutes' => 15,
                    'locale' => $mailContent['locale'],
                    'content' => $mailContent['content'],
                ]
            )
            ->setFrom([$fromEmail => Yii::$app->name . ' 团队'])
            ->setTo($email)
            ->setSubject($mailContent['content']['subject'])
            ->send();

            if ($result) {
                Yii::info("Password reset code email sent successfully to {$email}", __METHOD__);
            } else {
                Yii::warning("Failed to send password reset code email to {$email}", __METHOD__);
            }

            return $result;
        } catch (\Exception $e) {
            Yii::error("Error sending password reset code email to {$email}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    private function resolveMailContent(string $mailType, string $locale, array $i18n, int $expiryMinutes): array
    {
        $normalizedLocale = $this->normalizeLocale($locale);
        $defaults = self::MAIL_CONTENT_DEFAULTS[$mailType][self::DEFAULT_LOCALE];
        $resolvedLocale = self::DEFAULT_LOCALE;

        $custom = $this->extractLocaleContent($i18n, $normalizedLocale);
        if ($custom !== null) {
            $resolvedLocale = $normalizedLocale;
        } else {
            $custom = $this->extractLocaleContent($i18n, self::DEFAULT_LOCALE);
        }

        $content = array_merge($defaults, $custom ?? []);
        if (!isset($content['securityItems']) || !is_array($content['securityItems'])) {
            $content['securityItems'] = $defaults['securityItems'];
        }

        $replaceMap = [
            '{appName}' => Yii::$app->name,
            '{expiryMinutes}' => (string)$expiryMinutes,
        ];

        foreach (['subject', 'title', 'greeting', 'body', 'codeLabel', 'expiryText', 'securityTitle', 'footer'] as $field) {
            if (!isset($content[$field]) || !is_string($content[$field])) {
                $content[$field] = $defaults[$field];
            }
            $content[$field] = strtr($content[$field], $replaceMap);
        }

        $content['securityItems'] = array_map(function ($item) use ($replaceMap) {
            return is_string($item) ? strtr($item, $replaceMap) : '';
        }, $content['securityItems']);
        $content['securityItems'] = array_values(array_filter($content['securityItems'], function ($item) {
            return $item !== '';
        }));

        if (empty($content['securityItems'])) {
            $content['securityItems'] = $defaults['securityItems'];
        }

        return [
            'locale' => $resolvedLocale,
            'content' => $content,
        ];
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = trim($locale);
        if (preg_match('/^[a-z]{2}-[A-Z]{2}$/', $locale)) {
            return $locale;
        }

        return self::DEFAULT_LOCALE;
    }

    private function extractLocaleContent(array $i18n, string $locale): ?array
    {
        if (!isset($i18n[$locale]) || !is_array($i18n[$locale])) {
            return null;
        }

        return $i18n[$locale];
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

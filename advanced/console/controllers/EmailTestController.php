<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * 邮件功能测试控制器
 * 
 * 用于测试各种邮件发送功能
 * 
 * 使用方法：
 * php yii email-test/verification-code your@email.com
 * php yii email-test/password-reset your@email.com
 * php yii email-test/email-verify your@email.com
 * php yii email-test/all your@email.com
 */
class EmailTestController extends Controller
{
    /**
     * 测试验证码邮件
     * @param string $email 收件人邮箱
     * @return int
     */
    public function actionVerificationCode($email)
    {
        $this->stdout("正在发送验证码邮件到: {$email}\n", \yii\helpers\Console::FG_YELLOW);
        
        try {
            // 生成6位随机验证码
            $code = sprintf('%06d', mt_rand(0, 999999));
            $expiryMinutes = 15;
            
            // 使用环境变量中配置的发件人地址
            $fromEmail = getenv('MAILER_USERNAME') ?: 'dev@bujiaban.com';
            
            $result = Yii::$app->mailer->compose(
                ['html' => 'verificationCode-html', 'text' => 'verificationCode-text'],
                [
                    'code' => $code,
                    'expiryMinutes' => $expiryMinutes,
                ]
            )
                ->setFrom([$fromEmail => Yii::$app->name])
                ->setTo($email)
                ->setSubject('【' . Yii::$app->name . '】邮箱验证码')
                ->send();
            
            if ($result) {
                $this->stdout("✓ 验证码邮件发送成功！\n", \yii\helpers\Console::FG_GREEN);
                $this->stdout("验证码: {$code}\n", \yii\helpers\Console::FG_CYAN);
                $this->stdout("有效期: {$expiryMinutes} 分钟\n", \yii\helpers\Console::FG_CYAN);
                return ExitCode::OK;
            } else {
                $this->stdout("✗ 验证码邮件发送失败\n", \yii\helpers\Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } catch (\Exception $e) {
            $this->stdout("✗ 发送失败: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * 测试密码重置邮件
     * @param string $email 收件人邮箱
     * @return int
     */
    public function actionPasswordReset($email)
    {
        $this->stdout("正在发送密码重置邮件到: {$email}\n", \yii\helpers\Console::FG_YELLOW);
        
        try {
            // 生成模拟的重置令牌
            $token = Yii::$app->security->generateRandomString(32);
            $resetUrl = 'https://bujiaban.com/reset-password?token=' . $token;
            $expiryMinutes = 60;
            
            // 使用环境变量中配置的发件人地址
            $fromEmail = getenv('MAILER_USERNAME') ?: 'dev@bujiaban.com';
            
            $result = Yii::$app->mailer->compose(
                ['html' => 'passwordReset-html', 'text' => 'passwordReset-text'],
                [
                    'token' => $token,
                    'resetUrl' => $resetUrl,
                    'expiryMinutes' => $expiryMinutes,
                ]
            )
                ->setFrom([$fromEmail => Yii::$app->name])
                ->setTo($email)
                ->setSubject('【' . Yii::$app->name . '】密码重置请求')
                ->send();
            
            if ($result) {
                $this->stdout("✓ 密码重置邮件发送成功！\n", \yii\helpers\Console::FG_GREEN);
                $this->stdout("重置链接: {$resetUrl}\n", \yii\helpers\Console::FG_CYAN);
                $this->stdout("有效期: {$expiryMinutes} 分钟\n", \yii\helpers\Console::FG_CYAN);
                return ExitCode::OK;
            } else {
                $this->stdout("✗ 密码重置邮件发送失败\n", \yii\helpers\Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } catch (\Exception $e) {
            $this->stdout("✗ 发送失败: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * 测试邮箱验证邮件（带链接）
     * @param string $email 收件人邮箱
     * @return int
     */
    public function actionEmailVerify($email)
    {
        $this->stdout("正在发送邮箱验证邮件到: {$email}\n", \yii\helpers\Console::FG_YELLOW);
        
        try {
            // 创建模拟用户对象
            $user = new \stdClass();
            $user->username = explode('@', $email)[0];
            $user->verification_token = Yii::$app->security->generateRandomString(32);
            
            // 使用环境变量中配置的发件人地址
            $fromEmail = getenv('MAILER_USERNAME') ?: 'dev@bujiaban.com';
            
            $result = Yii::$app->mailer->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
                ->setFrom([$fromEmail => Yii::$app->name])
                ->setTo($email)
                ->setSubject('【' . Yii::$app->name . '】邮箱验证')
                ->send();
            
            if ($result) {
                $this->stdout("✓ 邮箱验证邮件发送成功！\n", \yii\helpers\Console::FG_GREEN);
                $this->stdout("验证令牌: {$user->verification_token}\n", \yii\helpers\Console::FG_CYAN);
                return ExitCode::OK;
            } else {
                $this->stdout("✗ 邮箱验证邮件发送失败\n", \yii\helpers\Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } catch (\Exception $e) {
            $this->stdout("✗ 发送失败: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * 测试简单文本邮件
     * @param string $email 收件人邮箱
     * @return int
     */
    public function actionSimple($email)
    {
        $this->stdout("正在发送简单测试邮件到: {$email}\n", \yii\helpers\Console::FG_YELLOW);
        
        try {
            // 使用环境变量中配置的发件人地址
            $fromEmail = getenv('MAILER_USERNAME') ?: 'dev@bujiaban.com';
            
            $result = Yii::$app->mailer->compose()
                ->setFrom([$fromEmail => Yii::$app->name])
                ->setTo($email)
                ->setSubject('【' . Yii::$app->name . '】邮件功能测试')
                ->setTextBody('这是一封测试邮件，用于验证邮件发送功能是否正常。')
                ->setHtmlBody('<h1>邮件功能测试</h1><p>这是一封测试邮件，用于验证邮件发送功能是否正常。</p><p>发送时间: ' . date('Y-m-d H:i:s') . '</p>')
                ->send();
            
            if ($result) {
                $this->stdout("✓ 简单测试邮件发送成功！\n", \yii\helpers\Console::FG_GREEN);
                return ExitCode::OK;
            } else {
                $this->stdout("✗ 简单测试邮件发送失败\n", \yii\helpers\Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } catch (\Exception $e) {
            $this->stdout("✗ 发送失败: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * 测试所有邮件类型
     * @param string $email 收件人邮箱
     * @return int
     */
    public function actionAll($email)
    {
        $this->stdout("\n========================================\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("开始测试所有邮件功能\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("收件人: {$email}\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("========================================\n\n", \yii\helpers\Console::FG_CYAN);
        
        $results = [];
        
        // 1. 简单测试邮件
        $this->stdout("[1/4] ", \yii\helpers\Console::FG_YELLOW);
        $results['simple'] = $this->actionSimple($email);
        sleep(2);
        
        // 2. 验证码邮件
        $this->stdout("\n[2/4] ", \yii\helpers\Console::FG_YELLOW);
        $results['verification_code'] = $this->actionVerificationCode($email);
        sleep(2);
        
        // 3. 密码重置邮件
        $this->stdout("\n[3/4] ", \yii\helpers\Console::FG_YELLOW);
        $results['password_reset'] = $this->actionPasswordReset($email);
        sleep(2);
        
        // 4. 邮箱验证邮件
        $this->stdout("\n[4/4] ", \yii\helpers\Console::FG_YELLOW);
        $results['email_verify'] = $this->actionEmailVerify($email);
        
        // 汇总结果
        $this->stdout("\n========================================\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("测试结果汇总\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("========================================\n", \yii\helpers\Console::FG_CYAN);
        
        $success = 0;
        $failed = 0;
        
        foreach ($results as $type => $result) {
            if ($result === ExitCode::OK) {
                $this->stdout("✓ ", \yii\helpers\Console::FG_GREEN);
                $success++;
            } else {
                $this->stdout("✗ ", \yii\helpers\Console::FG_RED);
                $failed++;
            }
            $this->stdout(ucfirst(str_replace('_', ' ', $type)) . "\n");
        }
        
        $this->stdout("\n成功: {$success} / 失败: {$failed}\n", 
            $failed > 0 ? \yii\helpers\Console::FG_YELLOW : \yii\helpers\Console::FG_GREEN);
        
        return $failed > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }
    
    /**
     * 显示帮助信息
     * @return int
     */
    public function actionIndex()
    {
        $this->stdout("\n邮件功能测试工具\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("================\n\n", \yii\helpers\Console::FG_CYAN);
        
        $this->stdout("可用命令:\n\n", \yii\helpers\Console::BOLD);
        
        $commands = [
            'simple <email>' => '发送简单测试邮件',
            'verification-code <email>' => '发送验证码邮件',
            'password-reset <email>' => '发送密码重置邮件',
            'email-verify <email>' => '发送邮箱验证邮件',
            'all <email>' => '测试所有邮件类型',
        ];
        
        foreach ($commands as $cmd => $desc) {
            $this->stdout("  php yii email-test/{$cmd}\n", \yii\helpers\Console::FG_GREEN);
            $this->stdout("    {$desc}\n\n");
        }
        
        $this->stdout("示例:\n", \yii\helpers\Console::BOLD);
        $this->stdout("  php yii email-test/all nethz@163.com\n\n", \yii\helpers\Console::FG_YELLOW);
        
        return ExitCode::OK;
    }
}

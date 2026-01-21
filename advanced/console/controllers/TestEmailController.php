<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;

/**
 * 邮件测试控制器
 */
class TestEmailController extends Controller
{
    /**
     * 发送测试邮件
     * @param string $to 收件人邮箱
     */
    public function actionSend($to)
    {
        echo "正在发送测试邮件到: $to\n\n";

        try {
            $mailer = Yii::$app->mailer;
            $message = $mailer->compose()
                ->setTo($to)
                ->setFrom([getenv('MAILER_USERNAME') => 'Yii2 测试系统'])
                ->setSubject('Yii2 邮件功能测试 - Symfony Mailer')
                ->setTextBody('这是一封测试邮件，用于验证 Yii2 + Symfony Mailer 邮件功能是否正常工作。')
                ->setHtmlBody('<h1>邮件功能测试</h1><p>这是一封测试邮件，用于验证 Yii2 + Symfony Mailer 邮件功能是否正常工作。</p><p>发送时间: ' . date('Y-m-d H:i:s') . '</p><p>使用库: Symfony Mailer (最新版)</p>');
            
            $result = $message->send();
            
            if ($result) {
                echo "✅ 邮件发送成功！\n";
                echo "收件人: $to\n";
                echo "发件人: " . getenv('MAILER_USERNAME') . "\n";
                echo "使用: Symfony Mailer (最新版)\n";
                return 0;
            } else {
                echo "❌ 邮件发送失败！\n";
                return 1;
            }
        } catch (\Exception $e) {
            echo "❌ 发送邮件时出错:\n";
            echo "错误信息: " . $e->getMessage() . "\n";
            echo "错误类型: " . get_class($e) . "\n";
            return 1;
        }
    }
}

#!/usr/bin/env php
<?php
/**
 * 邮件功能测试脚本
 * 使用方法: docker-compose exec api php test-email.php <收件人邮箱>
 */

// 加载 Yii
require __DIR__ . '/advanced/vendor/autoload.php';
require __DIR__ . '/advanced/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/advanced/common/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/advanced/common/config/main.php',
    require __DIR__ . '/files/common/config/main-local.php'
);

new yii\console\Application($config);

// 获取收件人邮箱
$to = $argv[1] ?? null;

if (!$to) {
    echo "使用方法: php test-email.php <收件人邮箱>\n";
    echo "示例: php test-email.php test@example.com\n";
    exit(1);
}

echo "正在发送测试邮件到: $to\n";
echo "邮件服务器配置:\n";
echo "  SMTP 主机: " . Yii::$app->mailer->transport->getHost() . "\n";
echo "  SMTP 端口: " . Yii::$app->mailer->transport->getPort() . "\n";
echo "  SMTP 用户: " . Yii::$app->mailer->transport->getUsername() . "\n";
echo "  发件人: " . json_encode(Yii::$app->mailer->messageConfig['from']) . "\n";
echo "\n";

try {
    $result = Yii::$app->mailer->compose()
        ->setTo($to)
        ->setSubject('Yii2 邮件功能测试')
        ->setTextBody('这是一封测试邮件，用于验证 Yii2 邮件功能是否正常工作。')
        ->setHtmlBody('<h1>邮件功能测试</h1><p>这是一封测试邮件，用于验证 Yii2 邮件功能是否正常工作。</p><p>发送时间: ' . date('Y-m-d H:i:s') . '</p>')
        ->send();
    
    if ($result) {
        echo "✅ 邮件发送成功！\n";
        echo "请检查收件箱: $to\n";
        exit(0);
    } else {
        echo "❌ 邮件发送失败！\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ 发送邮件时出错:\n";
    echo "错误信息: " . $e->getMessage() . "\n";
    echo "错误追踪:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

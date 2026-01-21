<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $code string 验证码 */
/* @var $expiryMinutes int 过期时间（分钟） */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>邮箱验证码</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .code-box {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .info {
            color: #6c757d;
            font-size: 14px;
            margin-top: 15px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 邮箱验证码</h1>
        </div>
        
        <p>您好，</p>
        <p>您正在进行邮箱验证操作，您的验证码是：</p>
        
        <div class="code-box">
            <div class="code"><?= Html::encode($code) ?></div>
            <div class="info">请在 <?= Html::encode($expiryMinutes) ?> 分钟内完成验证</div>
        </div>
        
        <div class="warning">
            <p><strong>⚠️ 安全提示：</strong></p>
            <p>• 请勿将此验证码告诉任何人</p>
            <p>• 如果这不是您本人的操作，请忽略此邮件</p>
            <p>• 验证码将在 <?= Html::encode($expiryMinutes) ?> 分钟后失效</p>
        </div>
        
        <div class="footer">
            <p>此邮件由系统自动发送，请勿直接回复</p>
            <p>&copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

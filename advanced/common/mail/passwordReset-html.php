<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $token string 重置令牌 */
/* @var $resetUrl string 重置链接 */
/* @var $expiryMinutes int 过期时间（分钟） */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>密码重置</title>
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
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 15px 40px;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .reset-button:hover {
            background-color: #0056b3;
        }
        .token-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            word-break: break-all;
        }
        .token-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .token {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #495057;
        }
        .info {
            color: #6c757d;
            font-size: 14px;
            text-align: center;
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
            margin: 5px 0;
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
            <h1>🔐 密码重置请求</h1>
        </div>
        
        <p>您好，</p>
        <p>我们收到了您的密码重置请求。请点击下面的按钮重置您的密码：</p>
        
        <div class="button-container">
            <a href="<?= Html::encode($resetUrl) ?>" class="reset-button">重置密码</a>
        </div>
        
        <div class="info">
            或者复制以下链接到浏览器中打开：<br>
            <a href="<?= Html::encode($resetUrl) ?>"><?= Html::encode($resetUrl) ?></a>
        </div>
        
        <div class="token-box">
            <div class="token-label">如果链接无法点击，您也可以使用以下令牌：</div>
            <div class="token"><?= Html::encode($token) ?></div>
        </div>
        
        <div class="warning">
            <p><strong>⚠️ 安全提示：</strong></p>
            <p>• 此链接将在 <?= Html::encode($expiryMinutes) ?> 分钟后失效</p>
            <p>• 如果这不是您本人的操作，请忽略此邮件并确保您的账户安全</p>
            <p>• 重置密码后，您需要使用新密码重新登录所有设备</p>
            <p>• 请勿将此链接或令牌告诉任何人</p>
        </div>
        
        <div class="footer">
            <p>此邮件由系统自动发送，请勿直接回复</p>
            <p>&copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

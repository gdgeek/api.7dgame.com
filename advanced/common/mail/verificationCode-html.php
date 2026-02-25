<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $code string 验证码 */
/* @var $expiryMinutes int 过期时间（分钟） */
/* @var $locale string 当前语言 */
/* @var $content array 邮件文案 */

$defaultContent = [
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
];

$content = is_array($content ?? null) ? array_merge($defaultContent, $content) : $defaultContent;
$content['expiryText'] = strtr((string)$content['expiryText'], ['{expiryMinutes}' => (string)$expiryMinutes]);

$securityItems = $content['securityItems'];
if (!is_array($securityItems)) {
    $securityItems = $defaultContent['securityItems'];
}
$securityItems = array_values(array_filter(array_map(function ($item) use ($expiryMinutes) {
    if (!is_string($item)) {
        return '';
    }
    return strtr($item, ['{expiryMinutes}' => (string)$expiryMinutes]);
}, $securityItems), function ($item) {
    return $item !== '';
}));
if (empty($securityItems)) {
    $securityItems = $defaultContent['securityItems'];
}
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($locale ?? 'en-US') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($content['title']) ?></title>
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
        .code-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
            text-transform: uppercase;
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
        .warning-title {
            margin: 0 0 8px 0;
            color: #856404;
            font-weight: bold;
            font-size: 14px;
        }
        .warning-list {
            margin: 0;
            padding-left: 18px;
            color: #856404;
            font-size: 14px;
        }
        .warning-list li {
            margin: 4px 0;
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
            <h1><?= Html::encode($content['title']) ?></h1>
        </div>

        <p><?= Html::encode($content['greeting']) ?></p>
        <p><?= Html::encode($content['body']) ?></p>

        <div class="code-box">
            <div class="code-label"><?= Html::encode($content['codeLabel']) ?></div>
            <div class="code"><?= Html::encode($code) ?></div>
            <div class="info"><?= Html::encode($content['expiryText']) ?></div>
        </div>

        <div class="warning">
            <p class="warning-title"><?= Html::encode($content['securityTitle']) ?></p>
            <ul class="warning-list">
                <?php foreach ($securityItems as $item): ?>
                    <li><?= Html::encode($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="footer">
            <p><?= Html::encode($content['footer']) ?></p>
            <p>&copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

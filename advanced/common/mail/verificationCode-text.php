<?php
/* @var $this yii\web\View */
/* @var $code string 验证码 */
/* @var $expiryMinutes int 过期时间（分钟） */
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
<?= $content['title'] . "\n" ?>
================

<?= $content['greeting'] . "\n\n" ?>
<?= $content['body'] . "\n\n" ?>
<?= $content['codeLabel'] ?>: <?= $code . "\n\n" ?>
<?= $content['expiryText'] . "\n\n" ?>
<?= $content['securityTitle'] ?>:
<?php foreach ($securityItems as $item): ?>
- <?= $item . "\n" ?>
<?php endforeach; ?>

<?= "\n" . $content['footer'] . "\n\n" ?>
© <?= date('Y') ?> <?= Yii::$app->name ?>. All rights reserved.

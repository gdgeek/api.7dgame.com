<?php
/* @var $this yii\web\View */
/* @var $code string 验证码 */
/* @var $expiryMinutes int 过期时间（分钟） */
?>
邮箱验证码
================

您好，

您正在进行邮箱验证操作，您的验证码是：

<?= $code ?>


请在 <?= $expiryMinutes ?> 分钟内完成验证。

安全提示：
• 请勿将此验证码告诉任何人
• 如果这不是您本人的操作，请忽略此邮件
• 验证码将在 <?= $expiryMinutes ?> 分钟后失效

此邮件由系统自动发送，请勿直接回复。

© <?= date('Y') ?> <?= Yii::$app->name ?>. All rights reserved.

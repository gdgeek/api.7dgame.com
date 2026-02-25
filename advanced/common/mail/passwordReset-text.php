<?php
/* @var $this yii\web\View */
/* @var $code string 重置验证码 */
/* @var $expiryMinutes int 过期时间（分钟） */
?>
密码重置请求
================

您好，

我们收到了您的密码重置请求。请使用以下验证码完成重置：

<?= $code ?>


安全提示：
• 此验证码将在 <?= $expiryMinutes ?> 分钟后失效
• 如果这不是您本人的操作，请忽略此邮件并确保您的账户安全
• 重置密码后，您需要使用新密码重新登录所有设备
• 请勿将验证码告诉任何人

此邮件由系统自动发送，请勿直接回复。

© <?= date('Y') ?> <?= Yii::$app->name ?>. All rights reserved.

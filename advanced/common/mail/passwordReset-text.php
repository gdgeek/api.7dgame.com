<?php
/* @var $this yii\web\View */
/* @var $token string 重置令牌 */
/* @var $resetUrl string 重置链接 */
/* @var $expiryMinutes int 过期时间（分钟） */
?>
密码重置请求
================

您好，

我们收到了您的密码重置请求。请访问以下链接重置您的密码：

<?= $resetUrl ?>


如果链接无法点击，您也可以使用以下令牌：

<?= $token ?>


安全提示：
• 此链接将在 <?= $expiryMinutes ?> 分钟后失效
• 如果这不是您本人的操作，请忽略此邮件并确保您的账户安全
• 重置密码后，您需要使用新密码重新登录所有设备
• 请勿将此链接或令牌告诉任何人

此邮件由系统自动发送，请勿直接回复。

© <?= date('Y') ?> <?= Yii::$app->name ?>. All rights reserved.

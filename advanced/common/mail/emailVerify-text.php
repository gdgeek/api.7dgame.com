<?php
/* @var $this yii\web\View */
/* @var $user api\modules\v1\models\User */

// 生成验证链接（使用完整URL）
$verifyLink = 'https://bujiaban.com/site/verify-email?token=' . $user->verification_token;
?>
您好 <?= $user->username ?>,

点击下面链接用以验证您的邮箱地址:

<?= $verifyLink ?>

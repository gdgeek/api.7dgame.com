<?php
use yii\helpers\Url;
$verifyLink = Url::to('@mrpp/#/site/binded-email?token='.$user->verification_token); 
?>
您好 <?= $user->username ?>,

点击下面链接用以验证您的邮箱地址:

<?= $verifyLink ?>

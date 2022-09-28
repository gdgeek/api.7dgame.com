<?php
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $user api\modules\v1\models\User */
//api.mrpp.com/site/verify-email?token=xxxxxx
//mrpp.com/#/site/verify-email?token=xxxxxx
$verifyLink = Url::to('@mrpp/site/verify-email?token='.$user->verification_token); //Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
您好 <?= $user->username ?>,

点击下面链接用以验证您的邮箱地址:

<?= $verifyLink ?>

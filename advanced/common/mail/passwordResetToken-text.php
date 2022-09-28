<?php

use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $user api\modules\v1\models\User */

$resetLink = Url::to('@mrpp/site/reset-password?token='.$user->password_reset_token); //Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
您好 <?= $user->username ?>,

通过下面链接来重新设置您的密码:

<?= $resetLink ?>

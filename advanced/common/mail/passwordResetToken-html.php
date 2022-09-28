<?php
use yii\helpers\Html;

use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $user api\modules\v1\models\User */
$resetLink =  Url::to('@mrpp/site/reset-password?token='.$user->password_reset_token);
//$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<div class="password-reset">
    <p>您好 <?= Html::encode($user->username) ?>,</p>

    <p>通过下面链接来重新设置您的密码:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>

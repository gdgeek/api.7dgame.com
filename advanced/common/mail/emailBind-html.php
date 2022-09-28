<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user api\modules\v1\models\User */

$verifyLink = Url::to('@mrpp/#/site/binded-email?token='.$user->verification_token);
?>
<div class="verify-email">
    <p>您好 <?= Html::encode($user->username) ?>,</p>

    <p>点击下面链接用以验证您的邮箱地址:</p>

    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>

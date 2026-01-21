<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user api\modules\v1\models\User */

// 生成验证链接（使用完整URL）
$verifyLink = 'https://bujiaban.com/site/verify-email?token=' . $user->verification_token;
?>
<div class="verify-email">
    <p>您好 <?= Html::encode($user->username) ?>,</p>

    <p>点击下面链接用以验证您的邮箱地址:</p>

    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>

<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\SignupForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = '注册';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('content-header');?>
	注册新账号
<?php $this->endBlock();?>


<?php $this->beginBlock('content-main');?>


		 <?php $form = ActiveForm::begin(['id' => 'form-signup']);?>

                <?=$form->field($model, 'username')->textInput(['autofocus' => true])->label(Yii::t('app', 'Username'))?>
                <?=$form->field($model, 'email')->label(Yii::t('app', 'Email'))?>
                <?=$form->field($model, 'password')->passwordInput()->label(Yii::t('app', 'Password'))?>
                <?=$form->field($model, 'invitation')->label(Yii::t('app', 'Invitation'))?>

                <div class="form-group">
                    <?=Html::submitButton('注册', ['class' => 'btn btn-primary', 'name' => 'signup-button'])?>
                </div>

            <?php ActiveForm::end();?>
        <!-- /.social-auth-links -->
		<a href="<?=Url::toRoute('site/login')?>">登陆账号</a><br>
        <a href="<?=Url::toRoute('site/request-password-reset')?>">找回遗忘的密码</a><br>

			<?php
if (!Yii::$app->params['information']['local']) {
    ?>

		<hr>


			<?php
}

?>
<?php $this->endBlock();?>




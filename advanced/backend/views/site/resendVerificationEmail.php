<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Resend verification email';
$this->params['breadcrumbs'][] = $this->title;
?>




<?php $this->beginBlock('content-header'); ?>
	<?= Html::encode($this->title) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('content-main'); ?>
	 <p>Please fill out your email. A verification email will be sent there.</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'resend-verification-email-form']); ?>
2
            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
1
            <div class="form-group">
                <?= Html::submitButton('Send', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
<?php $this->endBlock(); ?>


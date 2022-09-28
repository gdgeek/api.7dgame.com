<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

use common\widgets\Alert;
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = '重置密码';

?>



<?php $this->beginBlock('content-header'); ?>
	重置密码
<?php $this->endBlock(); ?>

<?php $this->beginBlock('content-main'); ?>
	 <p>1Please fill out your email. A verification email will be sent there.</p>

   
	<?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

		<?= $form->field($model, 'password')->passwordInput(['autofocus' => true]) ?>

		<div class="form-group">
			<?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
		</div>

	<?php ActiveForm::end(); ?>

	<!-- /.social-auth-links -->
		
	<a href="<?=Url::toRoute('site/signup') ?>" class="text-center">注册新账号</a><br>
	<a href="<?=Url::toRoute('site/request-password-reset') ?>">找回遗忘的密码</a>
<?php $this->endBlock(); ?>






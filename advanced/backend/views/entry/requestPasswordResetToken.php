<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

use common\widgets\Alert;
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = '找回遗忘的密码';


?>


<?php $this->beginBlock('content-header'); ?>
	找回遗忘的密码
<?php $this->endBlock(); ?>

<?php $this->beginBlock('content-main'); ?>
	 <div class="login-box-msg">请填写你的电子邮件。一个重置密码的链接将被发送到那里。</div>
		<?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

                <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>

                <div class="form-group">
                    <?= Html::submitButton('确认', ['class' => 'btn btn-primary']) ?>
                </div>

            <?php ActiveForm::end(); ?>

        <!-- /.social-auth-links -->

        <a href="<?=Url::toRoute('site/login') ?>">登陆账号</a><br>
        <a href="<?=Url::toRoute('site/signup') ?>" class="text-center">注册新账号</a>
<?php $this->endBlock(); ?>


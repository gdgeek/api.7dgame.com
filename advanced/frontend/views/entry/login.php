<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

use common\widgets\Alert;
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = '登陆';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-user form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
?>



<div class="text-center mb-3">
                
    <h1 class="h2 mb-2">继续探索</h1>
    <span>进入另外的世界。</span>
                  
</div>
		    
<div class="mb-3">
	<?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <?= $form
            ->field($model, 'username', $fieldOptions1)
            ->label(false)
            ->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>

        <?= $form
            ->field($model, 'password', $fieldOptions2)
            ->label(false)
            ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

            <div class="col-xs-8">
                <?= $form->field($model, 'rememberMe')->checkbox(array('label'=>Yii::t('app', 'Remember Me'))) ?>
            </div>
            <!-- /.col -->
            <div class="col-xs-4">
                <?= Html::submitButton('登陆', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
            <!-- /.col -->
        

        <?php ActiveForm::end(); ?>
    
    
</div>

           
<div class="text-center">

    <span class="text-small">无法登录?  <a href="<?=Url::toRoute('signup') ?>" class="text-center">在这里注册</a><br/>
   <?php /*或者  <a href="<?=Url::toRoute('request-password-reset') ?>" class="text-center">找回遗忘的密码</a></span>*/ ?>
</div>

          


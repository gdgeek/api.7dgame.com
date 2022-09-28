<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Info */
/* @var $form ActiveForm */




$this->registerJs("

	$('#goto').click(function(){
	
		var pattern = /^1[34578]\d{9}$/; 
		if(pattern.test($('#wechat').val())){
			$(location).attr('href', '".Url::toRoute(['info/create-invitation'])."?tel='+$('#wechat').val());
			
		}else{
			alert('您输入的微信号有误');
		}
	});

");



?>




<?php $this->beginBlock('content-header'); ?>
	申请测试码
<?php $this->endBlock(); ?>

<?php $this->beginBlock('content-main'); ?>
<div class="Info">

			<?php $form = ActiveForm::begin(); ?>

				<?= $form->field($model, 'name') ?>
				<?= $form->field($model, 'company') ?>
				<?= $form->field($model, 'tel') ?>
				<?= $form->field($model, 'reason')->textarea(['rows'=>'3']) ?>
				<p>
				<b>注意：</b> 这个网站的老板很闲，经常会翻看用户的申请信息，如果发现您的信息是虚假的，或者电话找不到您，肯定会删除您好不容易申请的账号。
				</p>
				<div class="form-group">
					<?= Html::submitButton('提交申请', ['class' => 'btn btn-primary']) ?>
				</div>
			<?php ActiveForm::end(); ?>

		</div><!-- Info -->

<?php $this->endBlock(); ?>

<?php $this->beginBlock('content-footer'); ?>
	  <div class="input-group">
		<input id='wechat' type="text" class="form-control" placeholder='通过微信找回' >
		<div class="input-group-btn">
			<?= Html::button('找回测试码', ['id'=>'goto', 'class' => 'btn btn-primary']) ?>
		</div>
	</div>
<?php $this->endBlock(); ?>

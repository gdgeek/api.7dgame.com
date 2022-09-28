<?php

use yii\helpers\Url;

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = '创建账号';
$this->params['breadcrumbs'][] = $this->title;



$circle = '<i class="fas fa-check-circle text-green"></i>';
$globe = '<i class="fas fa-globe text-aqua"></i>';



/* @var $this yii\web\View */
?>


                <div class="text-center mb-3">
                
                  <h1 class="h2 mb-2">在这里开始</h1>
                  <span>一切的故事的起点，从这个账号诞生。</span>
                  
                </div>
		    
                <div class="mb-3">
		             <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                            <?= $form->field($model, 'username')->textInput(['autofocus' => true])->label(Yii::t('app', 'Username')) ?>
                            <?= $form->field($model, 'email')->label(Yii::t('app', 'Email')) ?>
                            <?= $form->field($model, 'password')->passwordInput()->label(Yii::t('app', 'Password')) ?>
                            <?= $form->field($model, 'invitation')->label(Yii::t('app', 'Invitation')) ?>
				
                            <div class="text-center mt-4">
                                <?= Html::submitButton('创建账号', ['class' => 'btn btn-lg btn-success', 'name' => 'signup-button']) ?>
                            </div>

                     <?php ActiveForm::end(); ?>
    
    
                </div>

           
                <div class="text-center">
                   <span class="text-small">你已经有账号了? <a href="<?=Url::toRoute(["login"]) ?>">在这里登录</a></span>
                </div>

          


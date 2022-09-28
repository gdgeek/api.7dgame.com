<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\FeedbackDescribe */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="feedback-describe-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'describe')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'order')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

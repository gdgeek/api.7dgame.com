<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Feedback */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="feedback-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'reporter')->textInput() ?>

    <?= $form->field($model, 'repairer')->textInput() ?>

    <?= $form->field($model, 'describe_id')->textInput() ?>

    <?= $form->field($model, 'bug')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'debug')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'infomation')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

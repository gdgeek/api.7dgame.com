<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Method */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="method-form">

    <?php $form = ActiveForm::begin(); ?>

   

    <?= $form->field($model, 'definition')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'generator')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('导入', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

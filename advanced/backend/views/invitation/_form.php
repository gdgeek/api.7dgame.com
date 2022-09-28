<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Invitation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="invitation-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sender_id')->textInput() ?>

    <?= $form->field($model, 'recipient_id')->textInput() ?>

    <?= $form->field($model, 'used')->textInput() ?>

    <?= $form->field($model, 'auth_item_name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

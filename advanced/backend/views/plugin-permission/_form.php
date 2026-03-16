<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PluginPermissionConfig */
/* @var $form yii\widgets\ActiveForm */

$authManager = Yii::$app->authManager;
$roles = ArrayHelper::map($authManager->getRoles(), 'name', function ($role) {
    return $role->name . ($role->description ? " ({$role->description})" : '');
});
$permissions = ArrayHelper::map($authManager->getPermissions(), 'name', function ($perm) {
    return $perm->name . ($perm->description ? " ({$perm->description})" : '');
});
ksort($roles);
ksort($permissions);
$dropdownItems = ['角色' => $roles, '权限' => $permissions];
?>

<div class="plugin-permission-config-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'role_or_permission')->dropDownList($dropdownItems, ['prompt' => '-- 请选择角色或权限 --']) ?>

    <?= $form->field($model, 'plugin_name')->textInput(['maxlength' => 128]) ?>

    <?= $form->field($model, 'action')->textInput(['maxlength' => 128]) ?>

    <div class="form-group">
        <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

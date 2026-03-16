<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PluginPermissionConfig */

$this->title = '编辑插件权限配置: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '插件权限配置', 'url' => ['index']];
$this->params['breadcrumbs'][] = '编辑';
?>
<div class="plugin-permission-config-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

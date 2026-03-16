<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PluginPermissionConfig */

$this->title = '新增插件权限配置';
$this->params['breadcrumbs'][] = ['label' => '插件权限配置', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plugin-permission-config-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

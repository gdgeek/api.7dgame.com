<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Picture */

$this->title = \Yii::t('app', 'Upload Picture');
$this->params['breadcrumbs'][] = ['label' => 'Pictures', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="picture-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
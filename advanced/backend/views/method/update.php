<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Method */

$this->title = 'Update Method: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Methods', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="method-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

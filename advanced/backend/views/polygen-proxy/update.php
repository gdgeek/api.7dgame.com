<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Polygen */

$this->title = 'Update Polygen: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Polygens', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="polygen-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

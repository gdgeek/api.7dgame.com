<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\FeedbackState */

$this->title = 'Update Feedback State: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Feedback States', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="feedback-state-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\FeedbackDescribe */

$this->title = 'Update Feedback Describe: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Feedback Describes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="feedback-describe-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

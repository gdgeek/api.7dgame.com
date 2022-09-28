<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\FeedbackState */

$this->title = 'Create Feedback State';
$this->params['breadcrumbs'][] = ['label' => 'Feedback States', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feedback-state-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

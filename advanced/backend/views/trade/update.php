<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model api\modules\v1\models\Trade */

$this->title = 'Update Trade: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Trades', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="trade-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

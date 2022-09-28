<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Polygen */

$this->title = 'Create Polygen';
$this->params['breadcrumbs'][] = ['label' => 'Polygens', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="polygen-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

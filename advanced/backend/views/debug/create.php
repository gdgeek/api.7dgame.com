<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Debug */

$this->title = 'Create Debug';
$this->params['breadcrumbs'][] = ['label' => 'Debugs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="debug-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Scripts */

$this->title = 'Create Scripts';
$this->params['breadcrumbs'][] = ['label' => 'Scripts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="scripts-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

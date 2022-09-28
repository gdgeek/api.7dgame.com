<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Blockly */

$this->title = Yii::t('app', 'Create Blockly');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Blocklies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blockly-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

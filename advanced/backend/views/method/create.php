<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Method */

$this->title = '脚本导入';
$this->params['breadcrumbs'][] = ['label' => 'Methods', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="method-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

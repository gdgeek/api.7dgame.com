<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Project */

$this->title = '创建工程';
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('content-header'); ?>

    <?= Html::encode($this->title) ?>

<?php $this->endBlock(); ?>
<div class="project-create">

    <?= $this->render('add_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Invitation */

$this->title = 'Create Invitation';
$this->params['breadcrumbs'][] = ['label' => 'Invitations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="invitation-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

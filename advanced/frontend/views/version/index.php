<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\VersionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Versions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="version-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Version', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'version',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>

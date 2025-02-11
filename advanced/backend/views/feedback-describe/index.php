<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\FeedbackDescribeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Feedback Describes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feedback-describe-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Feedback Describe', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'describe',
            'order',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>

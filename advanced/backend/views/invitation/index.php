<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\InvitationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Invitations';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="invitation-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Invitation', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'code',
            'sender_id',
            'recipient_id',
            'used',
            'auth_item_name',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>

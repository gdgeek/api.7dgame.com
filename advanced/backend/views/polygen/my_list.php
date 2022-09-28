<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PolygenSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '模型管理';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('content-header'); ?>
<?= Html::encode($this->title) ?>
<?php $this->endBlock(); ?>

<div class="polygen-index">

    <p>
        <?= Html::a('上传模型', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
			
            'name',
            'sharing',
            'created_at',
			
			['class' => 'yii\grid\ActionColumn',
                'header' => '操作',
                'template' => '{delete}',
            ],
			
			
        ],
    ]); ?>


</div>

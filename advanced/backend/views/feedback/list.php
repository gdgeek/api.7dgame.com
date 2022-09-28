<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\FeedbackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Feedbacks';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feedback-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Feedback', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            'reporter',

			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>'提交者',
				'format' => 'raw',
				'value' => function ($data){
					/*$js = "$('input:text[name=\\'title_".$data['id']."\\']').change(function(){
						postTitle(".$data['id'].", this.value);
					})";
					$this->registerJs($js);
					$options = ['class' => 'form-control ', 'maxlength' => 20];*/
					return 1;
				},
			],


            'describe_id',
            'bug:ntext',
            //'debug:ntext',
            //'infomation:ntext',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>

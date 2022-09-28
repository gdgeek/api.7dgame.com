<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */



$this->title = \Yii::t('app', 'Project Manager');
$this->params['breadcrumbs'][] = $this->title;



 $js = '
	function postTitle(projectId, title){
		$.ajax({
		  type: "POST",
		  url: "'.Url::toRoute(['project/change-title']).'?id="+projectId,
		  data: {"title":title},
		  success: function(ret){
			//alert(ret);
		  }
		});

	}

	function postSharing(projectId, value){
	
		$.ajax({
		  type: "POST",
		  url: "'.Url::toRoute(['project/change-sharing']).'?id="+projectId,
		  data: {"sharing":value},
		  success: function(ret){
			//alert(ret);
		  }
		});

	}
 
 ';
 $this->registerJs($js, \yii\web\View::POS_END);

?>



<?php 
Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title">二维码</h4>',
    'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal" onclick="clearwindow()">关闭</a>',
]); 
$requestUrl = Url::toRoute(['project/qrcode']);
$js = <<<JS
	function clearwindow(){
		$('.modal-body').html('');
		
	}
	function getUrl(id){
		 $.get('{$requestUrl}', {'id':id},
			function (data) {
				$('.modal-body').html(data);
			}  
		);
	}
   
JS;
$this->registerJs($js, \yii\web\View::POS_END);
Modal::end(); 
?>

<?php $this->beginBlock('content-header'); ?>
<?= Html::encode($this->title) ?>
<?php $this->endBlock(); ?>

<div class="project-index">

<?php 


?>

    <p>
        <?= Html::a(\Yii::t('app', 'Create Project'), ['add-project'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
	
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
			
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>\Yii::t('app', 'Project Name'),
				'format' => 'raw',
				'value' => function ($data){
					$options = ['class' => 'form-control ', 'maxlength' => 20, 'id' => $data['id'], 'onchange'=>"postTitle(".$data['id'].",this.value)"];
					return Html::input('text', 'title_'.$data['id'], $data['title'], $options);
				},
			],


			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>\Yii::t('app', 'info'),
				'format' => 'raw',
				'value' => function ($data) {
				
					$options = ['class' => 'btn btn-success btn-xs',
					'title' => $data['introduce'],
					'aria-label'=> $data['introduce'],
					'data-pjax'=>'0',
					'data-method'=>'post'
					];
					return  Html::a('<span class="glyphicon glyphicon-edit" aria-hidden="true"></span> '.\Yii::t('app', 'Info Edit'), 
					['input/index', 'project'=>$data['id']], 
					$options);
				},
			],
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>\Yii::t('app', 'Project Configuration'),
				'format' => 'raw',
				'value' => function ($data) {
				$options = ['class' => 'btn btn-success btn-xs',
					'title' => \Yii::t('app', 'Project Edit'),
					'aria-label'=> '场景编辑',
					'data-pjax'=>'0',
					'data-method'=>'post'
					];
				return  Html::a('<span class="glyphicon glyphicon-picture" aria-hidden="true"></span> '.\Yii::t('app', 'Project Edit'), 
					['editor/index', 'project'=>$data['id'], 'template' => 'configure'], 
					$options);
				},
			],
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=> \Yii::t('app', 'Logic Configuration'),
				'format' => 'raw',
				'value' => function ($data) {

				$options = ['class' => 'center btn btn-success btn-xs ',
					'title' => \Yii::t('app', 'Logic Configuration'),
					'aria-label'=> '逻辑编辑',
					'data-pjax'=>'0',
					'data-method'=>'post'
					];

				
				return  Html::a('<span class="glyphicon glyphicon-cog" aria-hidden="true"></span> '.\Yii::t('app', 'Logic Configuration'), 
					['blockly/index-advanced', 'project'=>$data['id'], 'template' => 'logic'], 
					$options);
				},
			],
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>\Yii::t('app', 'Is Public'),
				'format' => 'raw',
				'value' => function ($data) {

					$options = [ 'onchange'=>"postSharing(".$data['id'].",this.checked)", 'checked' =>  $data['sharing'] == 1? true:false];
					return  Html::input('checkbox', 'sharing', 'true', $options);
				},
			],
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>\Yii::t('app', 'Copy'),
				'format' => 'raw',
				'value' =>  function ($data){
				
					return Html::a(\Yii::t('app', 'Copy'), Url::toRoute(['project/copy', 'project_id'=>$data['id']]), [
						'class' => 'btn btn-success btn-xs',
					]);
				},
			],
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>\Yii::t('app', 'QR Code'),
				'format' => 'raw',
				'value' => function ($data){
				
				return Html::a('<span class="glyphicon glyphicon-qrcode" aria-hidden="true"></span>', '#', [
					'id' => 'create',
					'data-toggle' => 'modal',
					'data-target' => '#create-modal',
					'class' => 'btn btn-success btn-xs',
					'onmousedown' => 'getUrl('.$data['id'].')',
				]);
				},
			],
			['class' => 'yii\grid\ActionColumn',
                'header' => \Yii::t('app', 'Operating'),
                'template' => '{delete}',
            ],
        ],
    ]); ?>


</div>

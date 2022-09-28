<?php

use yii\helpers\Html;
use yii\grid\GridView;

use api\modules\v1\models\User;
/* @var $this yii\web\View */
/* @var $searchModel common\models\InvitationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '邀请管理';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('content-header'); ?>
<?= Html::encode($this->title) ?>
<?php $this->endBlock(); ?>

<div class="invitation-index">

    <p>
		

		<div class="btn-group">
			<a type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">增加邀请码 
				<span class="caret"></span>
			</a>
			<ul class="dropdown-menu" role="menu">
			<?php 
			foreach($authItems as $key => $value){
				?><li><?= Html::a($value->name, ['add-invitation', 'name'=>$value->name], ['data-method' => 'post',]) ?></li><?php
			}
			
			?>
			</ul>
		</div>


    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
			 ['class' => 'yii\grid\SerialColumn'],
			 [
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=> Yii::t('app', 'Code'),
				'format' => 'raw',
				'value' => function ($data) {
					return $data['code'];
				},
			],
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>'受邀请者',
				'format' => 'html',
				'value' => function ($data) {
                    //echo ($data);
					$user = $data->getRecipient()->one();
					if($user){
						return $user->username;
					}
					return "";
				},
			],

			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>'使用状态',
				'format' => 'html',
				'value' => function ($data) {
					if($data->used){
						return "已经作废";
					}
					return "";
				},
			],
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>Yii::t('app','Auth Item Name'),
				'format' => 'raw',
				'value' => function ($data) {
					return $data['auth_item_name'];
				},
			],
        ],
    ]); ?>


</div>


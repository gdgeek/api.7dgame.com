<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Project', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
			[
				'class' => 'yii\grid\DataColumn', //由于是默认类型，可以省略 
				'label'=>'选择场景',
				'format' => 'html',
				'value' => function ($data) {
					$input = new StdClass();
					$mession = new StdClass();
					$mession->name = '/files/project_'.$data['id'].'/mession.json';
					$mession->url = Url::home(true);
					$mession->cache = false;
					$mession->compress = false;
					$input->mession = $mession;
					$value = urlencode(json_encode($input, JSON_UNESCAPED_SLASHES));
					$url = 'https://mession?cb=callback&input='.$value;
					return '<a href="'.$url.'">'.$data['title'].'</a>'; // 如果是数组数据则为 $data['name'] ，例如，使用 SqlDataProvider 的情形。
				},
			],
        ],
    ]); ?>


</div>

<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\components\ResourceList;



/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '图片列表';

$this->params['breadcrumbs'][] = ['label' => '图片管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('content-header'); ?>
<?= Html::encode($this->title) ?>


<?php $this->endBlock(); ?>
<?= Html::a(\Yii::t('app', 'Upload Picture'), ['upload'], ['class' => 'btn btn-xs btn-success']) ?>
<?= ResourceList::widget([
    'dataProvider' => $dataProvider,
     'search' => 'ResourceSearch',
]) ?>
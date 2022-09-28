<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\components\PictureWidget;
use backend\components\ResourceView;
/* @var $this yii\web\View */
/* @var $model common\models\Picture */

$this->title = '图片展示';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pictures'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<div class="picture-view">

<?=  ResourceView::widget(['model' => $model,'render'=>'picture_view'
]) ?>


</div>


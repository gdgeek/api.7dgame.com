<?php


use backend\components\ResourceView;

/* @var $this yii\web\View */
/* @var $model backend\models\Video */

$this->title = '视频展示';
$this->params['breadcrumbs'][] = ['label' => '视频管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="video-view">

    <?=  ResourceView::widget(['model' => $model]) ?>

</div>

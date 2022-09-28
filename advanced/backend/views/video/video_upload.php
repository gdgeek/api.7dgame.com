<?php

use yii\helpers\Html;
use app\components\FileUploadWidget;
/* @var $this yii\web\View */
/* @var $model backend\models\Video */

$this->title = \Yii::t('app', 'Upload Video');
$this->params['breadcrumbs'][] = ['label' => 'Videos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= FileUploadWidget::widget([
    'model'=>$model,
    'fileType'=>'video/mp4,video/ogg',
    'button'=> \Yii::t('app', 'Upload Video'),
]) ?>


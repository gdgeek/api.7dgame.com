

<?php
use yii\helpers\Html;
use app\components\FileUploadWidget;
/* @var $this yii\web\View */
/* @var $model backend\models\Video */

$this->title = \Yii::t('app', 'Upload Picture');
$this->params['breadcrumbs'][] = ['label' => 'Picture', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= FileUploadWidget::widget([
    'model'=>$model,
    'fileType'=>"image/gif, image/jpeg, image/png",
    'button'=> \Yii::t('app', 'Upload Picture'),
]) ?>


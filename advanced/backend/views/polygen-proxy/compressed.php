<?php

use yii\helpers\Html;
use app\components\FileUploadWidget;

$this->title = \Yii::t('app', 'Compressed & Upload');
$this->params['breadcrumbs'][] = ['label' => 'Videos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= FileUploadWidget::widget([
    'model'=>$model,
    'fileType'=>'.fbx',
    'button'=> \Yii::t('app', 'Compressed & Upload'),
    'compressed'=>true,
]) 

?>

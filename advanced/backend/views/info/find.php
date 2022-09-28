<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Info */
/* @var $form ActiveForm */
?>




<?php $this->beginBlock('content-header'); ?>
	已经申请过测试码
<?php $this->endBlock(); ?>

<?php $this->beginBlock('content-main'); ?>

		您之前申请的测试码为<b><?=$invitation?></b>。
<?php $this->endBlock(); ?>

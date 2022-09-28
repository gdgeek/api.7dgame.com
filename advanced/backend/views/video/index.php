<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\components\ResourceList;



/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '视频列表';

$this->params['breadcrumbs'][] = ['label' => '视频管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('content-header'); ?>
<?= Html::encode($this->title) ?>

<?php $this->endBlock(); ?>
<?= Html::a(\Yii::t('app', 'Upload Video'), ['upload'], ['class' => 'btn btn-xs btn-success']) ?>
<?= ResourceList::widget([
    'dataProvider' => $dataProvider,
     'search' => 'VideoSearch',

]) ?>
   


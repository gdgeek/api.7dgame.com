<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\components\ModelView;

use backend\components\ResourceList;

$this->title = '模型列表';


$this->params['breadcrumbs'][] = ['label' => '模型管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$url = Url::current(['t'=>'0','ResourceSearch'=>null]);

$this->registerJs(
<<<EOF
function Search(self){
   window.location.href='$url&'+encodeURI('ResourceSearch[name]')+'='+$('#search').val();
}
EOF
,  \yii\web\View::POS_BEGIN);
$this->registerJs('
function OpenModal(id, self){
   // alert(self.value)
    $("#model_id").val(id);
    $("#model_name").val(self.value);
    $("#modal-default").modal();
}
',  \yii\web\View::POS_BEGIN);
?>


<?php $this->beginBlock('content-header'); ?>
<?= Html::encode($this->title) ?>


<?php $this->endBlock(); ?>

<?= Html::a('上传模型', ['polygen/upload'], ['class' => 'btn btn-success btn-xs']) ?>

<?= ResourceList::widget([
    'dataProvider' => $dataProvider,
     'search' => 'ResourceSearch',

]) ?>
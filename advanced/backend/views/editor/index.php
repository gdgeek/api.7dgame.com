<?php

use yii\helpers\Url;
use app\components\Template;
use app\components\EditorComponent;
use rmrevin\yii\fontawesome\FA;
use backend\assets\editor\BaseAsset;
use backend\assets\editor\ReteAsset;
ReteAsset::register($this);
BaseAsset::register($this);
$template = new Template('main', $project_id, -1);
$setup = $template->setup();
$ec = new EditorComponent('main');
$components = $ec->components();
$this->registerJs($ec->js(), \yii\web\View::POS_BEGIN);


$data = null;
$nodes_data = null;

$list = array();
if($datas){
	foreach($datas as $ed){
		if($ed->node_id == -1){
			$data = $ed->data;
		}else{
			$list[$ed->node_id] = true;
		}
	}
}
$js_list = json_encode($list);
$js_data = (isset($data)?$data:$setup->data);
$plugins = $setup->plugins;

?>


<?php $this->beginBlock('content-header'); ?>
<?=$setup->title?><br/>
<div class="btn-group"   role="group" >

    <a class="btn btn-primary btn-xs" href="<?=Url::toRoute(['project/index' ])?>"><?= FA::icon('arrow-circle-left') ?> 返回</a>

	<a class="btn btn-info btn-xs"  href="<?=Url::toRoute(['blockly/index-advanced', 'project' => $project_id ]);?>"> 逻辑编辑	</a>
	<a id="save" class="btn btn-success btn-xs disabled" style="disabled:true" type="button" href="#">
		<?=FA::icon('save')?>  <nobr id="save_text">保存</nobr>
	</a>
</div>

<?php $this->endBlock(); ?>

<?php echo $this->render('_edit',['project_id' => 1, 'components' =>$components, 'plugins'=>$setup->plugins ,'data'=>$js_data, 'list'=>$js_list] );?>
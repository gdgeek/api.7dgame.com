<?php
use rmrevin\yii\fontawesome\FA;
use backend\assets\editor\BaseAsset;
use backend\assets\editor\ReteAsset;
ReteAsset::register($this);
BaseAsset::register($this);

$template = \Yii::$app->getModule('template');
$setup = $template->setup('main', $project_id, -1);

$components = [];
foreach($setup->assets as $asset){
	
	($asset)::register($this);
	$components = ($asset)::registerComponents($components);
}
$data = null;
$nodes_data = null;

$list = array();
if($datas){
	foreach($datas as $ed){
		if($ed->node_id == -1){
			$data = $ed->data;
		}else{
			$list[$ed->node_id] = $ed->serialization;
		}
	}
}

$js_list = json_encode($list);
$js_data = (isset($data)?$data:$setup->data);

$plugins = $setup->plugins;

?>
<?php $this->beginBlock('content-header'); ?>
场景编辑
<div class="btn-group"   role="group" aria-label="...">
	<a id="save"  onclick="MrPP.DB.save()" class="btn btn-success btn-xs disabled" style="disabled:true" type="button" href="#">
		<?=FA::icon('save')?> 保存
	</a>
</div>

<?php $this->endBlock(); ?>
<?php echo $this->render('_edit',['project_id' => 1, 'components' =>$components, 'plugins'=>$setup->plugins ,'data'=>$js_data, 'list'=>$js_list] );?>
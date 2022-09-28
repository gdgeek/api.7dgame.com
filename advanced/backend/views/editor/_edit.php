<?php

use yii\helpers\Url;
use yii\helpers\Html;
use backend\assets\FontAsset;

use backend\assets\AFrameAsset;

AFrameAsset::register($this);
FontAsset::register($this);
$this->registerCss("
	.content{
		min-height:73vh;
		max-height:73vh;
		display:flex;
	}
	#rete{
		border-style:solid;
		border-width:1px;
	}

");
$js = "
var components = [
";
foreach ($components as $value) {
	$js .= "new " . $value . ",";
}
$js .= "];";




$this->registerJs($js);


$this->registerJs("
var engine = new Rete.Engine('MrPP@0.1.0');
");

$this->registerJs("
var container = document.querySelector('#rete')
const editor = new Rete.NodeEditor('MrPP@0.1.0', container);

let modules = {};
editor.silent = true;
MrPP.editor = editor;
MrPP.engine = engine;
editor.use(VueRenderPlugin.default);
editor.use(ConnectionPlugin.default);
editor.use(ModulePlugin.default, {engine, modules });
editor.use(AutoArrangePlugin.default, { margin: {x: 50, y: 50 }, depth: 0 });
editor.use(ContextMenuPlugin.default, {
    delay: 100,
    allocate(component) {
		if(component.type == ''){
			return [];
		}else{
			return component.type;
		}
    },
    rename(component) {
        return component.title;
    }

});
editor.use(AreaPlugin);
");

if (isset($data)) {
	$this->registerJs("var data = $data;");
}


if (isset($plugins['Node']) && isset($list)) {

	$this->registerJs("
		editor.use(MrPP.Node, {nodes:$list});
	");
}
if (isset($plugins['Internal'])) {
	$this->registerJs("
		editor.use(MrPP.Internal, {
				url:'" . $plugins['Internal']->url . "',
				removed_url:'" . $plugins['Internal']->removed_url . "',
				csrf:'<input type=\"hidden\" name=\"" . Yii::$app->request->csrfParam . "\" value=\"" . Yii::$app->request->getCsrfToken() . "\">'});
			");
}
if (isset($plugins['DB'])) {
	$this->registerJs("
		MrPP.DB.data = JSON.stringify(data);
		MrPP.DB.on_saving = function(){
			$('#save_text').text('保存中....');
		}	
		MrPP.DB.on_saved = function(){
			$('#save_text').text('保存完成');
		}
");
	$this->registerJs("editor.use(MrPP.DB, " . json_encode($plugins['DB']) . ");");
}

if (isset($plugins['Locked'])) {
	$this->registerJs("editor.use(MrPP.Locked, " . json_encode($plugins['Locked']) . ");");
}


if (isset($plugins['Polygens'])) {
	$this->registerJs("editor.use(MrPP.Polygens, " . json_encode($plugins['Polygens']) . ");");
}

if (isset($plugins['Videos'])) {
	$this->registerJs("editor.use(MrPP.Videos, " . json_encode($plugins['Videos']) . ");");
}

if (isset($plugins['Pictures'])) {
	$this->registerJs("editor.use(MrPP.Pictures, " . json_encode($plugins['Pictures']) . ");");
}
$this->registerJs("
var engine = new Rete.Engine('MrPP@0.1.0');

components.map(c => {
//	console.log(c)
	editor.register(c);
	engine.register(c);
});

editor.on('process connectioncreate connectionremove nodecreate noderemove', ()=>{
  if(editor.silent) return;
  compile();
});
editor.on('nodecreate', (node)=>{

	//alert(node.name);
	//editor.arrange(node);

});
async function compile() {
		await engine.abort();
    await engine.process(editor.toJSON());
}");

$this->registerJs("

editor.fromJSON(data).then(() => {
	editor.view.resize();
	AreaPlugin.zoomAt(editor);//缩放
	compile();
});
//editor.arrange(editor.nodes);

");

?>


<div id="rete"></div>


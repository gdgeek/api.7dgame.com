<?php

use yii\helpers\Url;
backend\assets\IdeAsset::register($this);

/* @var $this yii\web\View */
?>
<?php $this->beginBlock('content-header'); ?>

逻辑编辑

<?php $this->endBlock(); ?>
<a id="save" class="btn btn-success btn-xs disabled" href="#"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> 保存脚本(ctrl+s)</a><p/>
<pre id="editor"><?=$project->logic?></pre>


<?php
$css = "
.content{
	min-height:85vh;
}
#editor{
	min-height:80vh;
}
";
$this->registerCSS($css);
$this->registerJS("


    var editor = ace.edit('editor');
	var code = editor.getValue();
	function refresh(){
		if(code === editor.getValue()){
		
			$('#save').addClass('disabled');
		}else{
		
			$('#save').removeClass('disabled');

		}
	
	}
	
    editor.setTheme('ace/theme/xcode');
    editor.session.setMode('ace/mode/lua');
	
	$('#editor').keyup(function(e){
		refresh();
	});
	$('#editor').keydown(function(e){
		e = e||window.event;
		if( e.ctrlKey && e.keyCode === 83  )
		{	
			$('#save').click();
   			return false;
		}
		return true;
	});

	$('#save').click(function() {
	
		$.post({
			url:'".Url::toRoute('ide/submit')."',
			data:{'logic':editor.getValue(),
				 'project':".$project->id." 
				},
			success:function(result){
				code = editor.getValue();
				refresh();
			}
				
		});
	});

");
?>
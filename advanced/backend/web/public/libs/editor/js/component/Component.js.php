<?php
require('./Common.php');
$output = json_decode($_GET['output']);
if(!empty($_GET['items'])){
	$items = json_decode($_GET['items']);
}
$controls = $_GET['controls'];
?>
class <?=$_GET['name']?> extends Rete.Component {

	constructor() {
		super('<?=$_GET['name']?>');
	}

	builder(node) {
		var out = new Rete.Output('<?=$output->name?>', "<?=$output->title?>", <?=$output->socket?>);//输出
	
		return node
		<?php
		foreach($controls as $control){
			//echo ControlCreate($value);
			?>
			.addControl(new <?=ControlCreate($control)?>)
			<?php
		}
		?>
			
			.addOutput(out);//输出
	}

	worker(node, inputs, outputs) {
		var data = node.data;

		<?php
		if(!empty($items)){
			foreach($items as $key => $val){
				?>
				data['<?=$key?>'] = '<?=$val?>';
				<?php
			}
		}
		?>
		outputs['<?=$output->name?>'] = data;
	}
}

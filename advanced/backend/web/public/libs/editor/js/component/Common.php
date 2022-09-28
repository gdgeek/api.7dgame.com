<?php
function ControlCreate($input){

	$data = json_decode($input);
	switch($data->type){
		case 'string':
			return "StringControl(this.editor, '".$data->name."', '".$data->default."')";
		break;

		case 'vector2':
			return "Vector2Control(this.editor, '".$data->name."', ".$data->default.")";
		break;

		case 'vector3':
			return "Vector3Control(this.editor, '".$data->name."', ".$data->default.")";
		break;
		case 'select':
			return "SelectControl(this.editor, '".$data->name."', ".$data->options.",".$data->default.")";
		break;

		case 'bool':
			return "BoolControl(this.editor, '".$data->name."', ".$data->default.")";
		break;
	}
}
function ControlData($data){

	//$data = json_decode($input);
	switch($data->type){
		case 'string':
			return "StringControl(this.editor, '".$data->name."', '".$data->default."')";
		break;


		case 'vector2':
			return "Vector2Control(this.editor, '".$data->name."', ".$data->default.")";
		break;
		case 'vector3':
			return "Vector3Control(this.editor, '".$data->name."', ".$data->default.")";
		break;
		case 'select':
			return "SelectControl(this.editor, '".$data->name."', ".$data->options.",".$data->default.")";
		break;
		
		case 'bool':
			return "BoolControl(this.editor, '".$data->name."', ".$data->default.")";
		break;
		case 'number':
			return "NumberControl(this.editor, '".$data->name."', ".$data->default.")";
		break;
	}
}


function MackComponent($name, $controls, $items, $inputs, $output)
{
	?>
class <?=$name?> extends Rete.Component {

	constructor() {
		super('<?=$name?>');
	}

	builder(node) {
		var out = new Rete.Output('<?=$output->name?>', "<?=$output->title?>", <?=$output->socket?>);//输出
	
		return node
		<?php
		foreach($controls as $control){
			?>
			.addControl(new <?=ControlData($control)?>)
			<?php
		}
		?>
			

		<?php
		if(!empty($inputs)){
			foreach($inputs as $input){
				?>
				.addInput(new Rete.Input("<?=$input->name?>", "<?=$input->title?>", <?=$input->socket?>, <?=$input->multiple?"true":"false"?>))
				<?php
			}
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


<?php	

}
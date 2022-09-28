<?php

namespace common\components\editor;

class NodeMaterial{

	public function setup($node, $inputs, $reader, $map){
		//echo json_encode($node);
		$this->mode = $node->data->mode;
	}
}
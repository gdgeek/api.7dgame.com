<?php

namespace common\components\editor;


class Point extends Node{

	public function setup($node, $inputs, $reader, $map, $node_id){
		$this->id = $node->id;
        $this->title = Node::GetTitle($node, $node_id);
		$this->type = "point";
	
		if(isset($inputs["transform"][0])){
			$this->transform = $inputs["transform"][0];
		}
        if(isset($inputs['effects'])){
			$this->effects = $inputs['effects'];
		}	
	}
}

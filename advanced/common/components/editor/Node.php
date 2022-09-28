<?php

namespace common\components\editor;


class Node{

    public static function GetTitle($node, $node_id){
        if(isset($node->data->title)){
			return $node_id.'.'.$node->id.':'.$node->data->title;
		}else{
			return  $node_id.'.'.$node->id;
		}
    }
	public static function GetLabel($node, $node_id){
		$ret = new \stdClass();
        $ret->title = Point::GetTitle($node, $node_id);
		return $ret;
	}
}

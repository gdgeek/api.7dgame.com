<?php
/*
namespace app\components;
use yii\base\BaseObject;
use yii\helpers\Url;


class List extends BaseObject
{
    public $models;

    public function __construct($models, $config = [])
    {
        $this->models = $models;
     
        // ... 在应用配置之前初始化
        parent::__construct($config);
    }
    
	public function datas(){
		
		$list = array();
		foreach($models as $val){ 
			$data = new \stdClass();
			$data->id = $val->id;
			$data->name = $val->name;
			
			array_push($list, $data);
		} 
		return $list;
	}
}
*/
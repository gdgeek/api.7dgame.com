<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $searchModel common\models\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Projects';
switch($filename){
	case 'setup.json':

		$dataProvider = $searchModel->search(['id' => $id]);
		break;
	case 'logic.lua':
		echo 'lua';
		break;
	case 'mession.json':
		$mession = new StdClass();

		
		$logic = new StdClass();
		$logic->name = "/files/logic.lua";
		$logic->cache = false;
		$logic->compress = false;
		$logic->url ="http://localhost:8888/";
		$mession->logic = $logic;
		
		$configure = new StdClass();
		$configure->name = '/files/setup2.json.txt';
		$configure->cache = false;
		$configure->compress = false;
		$configure->url = 'http://localhost:8888';
		$mession->configure = $configure;

		$version = new StdClass();
		$version->major = 0;
		$version->minor = 1;
		$version->patch = 0;
		$mession->version = $version;
		echo json_encode($mession, JSON_UNESCAPED_SLASHES);
		break;
}
?>

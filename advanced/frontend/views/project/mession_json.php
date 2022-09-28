<?php

use yii\helpers\Html;
use yii\helpers\Url;



$mession = new StdClass();

		
$logic = new StdClass();
$logic->name ='/files/project_'.$id.'/logic.lua';
$logic->cache = false;
$logic->compress = false;
$logic->url = Url::home(true);
$mession->logic = $logic;
		
$configure = new StdClass();
$configure->name ='/files/project_'.$id.'/configure.json';
$configure->cache = false;
$configure->compress = false;
$configure->url = Url::home(true);
$mession->configure = $configure;

$version = new StdClass();
$version->major = 0;
$version->minor = 1;
$version->patch = 0;
$mession->version = $version;
echo json_encode($mession, JSON_UNESCAPED_SLASHES);

?>

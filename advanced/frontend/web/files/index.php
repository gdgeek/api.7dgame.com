<?php
$subject =  $_SERVER["REQUEST_URI"];
$pattern = '/(.*\/)files\/([A-Za-z]+)_([0-9]+)\/(.+)/';
if(preg_match($pattern, $subject, $matches)){
	$ret = [];
	$ret['root'] =  $matches[1];
	$ret['type'] =  $matches[2];
	$ret['id'] = urlencode($matches[3]);
	$ret['filename'] =  urlencode($matches[4]);
	$url= "http://".$_SERVER['HTTP_HOST'].$ret['root'].$ret['type'].'/file?id='.$ret['id'].'&filename='.$ret['filename'];
	//echo $url;
	$info= file_get_contents($url);
	header('Content-Type:text/plain; charset=utf-8');;
	echo $info;
}else{
    echo "no file!";
}
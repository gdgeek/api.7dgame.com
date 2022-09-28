<?php

namespace common\components;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Url;

use api\modules\v1\models\File;


/**
 * template module definition class
 */
class FileData// extends BaseObject
{
    
    public $cache;
    public $compressed;
    public $url;
    public $name;
    public $local;
    public function __construct($file, $cache, $compressed = null)
    {
       // $this->file = $file;
        $this->cache = $cache;
        $this->compressed = $compressed;
        
        
		//$file = $this->file;
    
		if($file != null){
            $pattern = '/(http[s]?:\/\/[^\/]+\/)(.+)/';
            preg_match($pattern, $file->url, $match);
            $this->name = $match[2];
            $this->url = $match[1];
        }

        $this->cache = $cache;
        $this->compressed = $compressed;

        if($compressed != null){
            $this->compress = true;
        }

        $this->location = $file->url;
	//	return $data;



        // ... 在应用配置之前初始化
     //   parent::__construct($config);
    }

    /**
     * {@inheritdoc}
   
    public function init()
    {
        parent::init();



        // custom initialization code goes here
    }

    public function createData()
    {
      

		$file = $this->file;
    
        $data = new \stdClass();
		if($file != null){
            $pattern = '/(http[s]?:\/\/[^\/]+\/)(.+)/';
            preg_match($pattern, $file->url,$match);
            $data->name = $match[2];
            $data->url = $match[1];
        }

        $data->cache = $this->cache;
        $data->compressed = $this->compressed;

        if($data->compressed != null){
            $data->compress = true;
        }

        $data->location = $file->url;
		return $data;
    }  */



}

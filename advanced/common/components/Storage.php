<?php

namespace common\components;

use yii\base\Component;
use yii\helpers\FileHelper;

class Storage extends Component
{

    public $root = 'storage';
    public $buckets = ['raw', 'store'];
    public $temp = "temp";

    public function init()
    {

        $mode = 0777;

        foreach ($this->buckets as $bucket) {
            $path = $this->root . '/' . $bucket;
            FileHelper::createDirectory($path, $mode);
        }
        FileHelper::createDirectory($this->root . '/' . $this->temp, $mode);
        return true;
    }
    public function targetDirector($bucket, $director)
    {
        if (in_array($bucket, $this->buckets)) {
            $mode = 0777;
            $path = $this->root . '/' . $bucket . '/' . $director . '/';

            FileHelper::createDirectory($path, $mode);
            return $path;

        }
        return false;
    }
    public function __get($vname)
    {

        if ($vname == 'tempDirector') {
            return $this->root . '/' . $this->temp . '/';
        }

        return null;

    }

}

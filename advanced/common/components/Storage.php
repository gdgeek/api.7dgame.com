<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;

class Storage extends Component
{

    public $root = 'storage';
    public $buckets = ['raw', 'store', 'temp', 'derived'];
    public $publicBuckets = ['store', 'derived'];
    public $temp = "temp";
    public $publicBaseUrl = '/storage';

    public function init()
    {
        $this->root = $this->resolveRoot();
        $this->publicBaseUrl = rtrim(getenv('LOCAL_STORAGE_PUBLIC_BASE_URL') ?: $this->publicBaseUrl, '/');

        $mode = 0777;

        foreach ($this->buckets as $bucket) {
            $path = $this->root . '/' . $bucket;
            FileHelper::createDirectory($path, $mode);
        }
        FileHelper::createDirectory($this->root . '/' . $this->temp, $mode);
        return true;
    }

    public function resolveRoot()
    {
        $root = getenv('LOCAL_STORAGE_ROOT') ?: $this->root;
        if ($root !== '' && $root[0] !== '/') {
            $root = Yii::getAlias('@app/../' . $root, false) ?: $root;
        }
        return rtrim($root, '/');
    }

    public function normalizeKey($key)
    {
        $key = trim((string)$key, '/');
        if ($key === '' || preg_match('/[\x00-\x1F\x7F]/', $key)) {
            throw new BadRequestHttpException('非法文件路径');
        }
        if (strpos($key, '\\') !== false || strpos($key, '..') !== false || str_starts_with($key, '/')) {
            throw new BadRequestHttpException('非法文件路径');
        }
        return $key;
    }

    public function bucketPath($bucket)
    {
        if (!in_array($bucket, $this->buckets, true)) {
            throw new BadRequestHttpException('非法存储桶');
        }
        return $this->root . '/' . $bucket;
    }

    public function path($bucket, $key)
    {
        return $this->bucketPath($bucket) . '/' . $this->normalizeKey($key);
    }

    public function publicUrl($bucket, $key)
    {
        if (!in_array($bucket, $this->publicBuckets, true)) {
            return null;
        }
        return $this->publicBaseUrl . '/' . rawurlencode($bucket) . '/' . str_replace('%2F', '/', rawurlencode($this->normalizeKey($key)));
    }

    public function targetDirector($bucket, $director)
    {
        if (in_array($bucket, $this->buckets, true)) {
            $mode = 0777;
            $director = trim((string)$director, '/');
            if ($director !== '') {
                $director = $this->normalizeKey($director);
            }
            $path = $this->root . '/' . $bucket . ($director === '' ? '' : '/' . $director) . '/';

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

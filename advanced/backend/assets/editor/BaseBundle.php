<?php

namespace backend\assets\editor;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class BaseBundle extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
    ];
    public $depends = [
        'backend\assets\editor\ReteAsset'
    ];

    function __construct()
    {
        $time = time();
        foreach (static::$components as $key => $url) {
            if (strpos($url, '?') !== false) {
                array_push($this->js, $url . '&' . $time);
            } else {
                array_push($this->js, $url . '?' . $time);
            }
        }
    }

    public static function registerComponents($components)
    {
        foreach (static::$components as $key => $value) {
            if (!in_array($key, $components)) {
                array_push($components, $key);
            }
        }
        return $components;
    }
}

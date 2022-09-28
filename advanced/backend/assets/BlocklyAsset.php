<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class BlocklyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'public/css/site.css',
    ];

    function __construct()
    {
        $time = time();
        array_push($this->js, 'public/libs/blockly/other/storage.js?' . $time);
        array_push($this->js, 'blockly/code-js?' . $time);
    }

    public $js = [
        'public/libs/blockly/blockly_compressed.js',
        'public/libs/blockly/blocks_compressed.js',
        'public/libs/blockly/lua_compressed.js',
        //'blockly/javascript_compressed.js',
        //'blockly/python_compressed.js',
        //'blockly/php_compressed.js',
        //'blockly/dart_compressed.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}

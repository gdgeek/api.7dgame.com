<?php

namespace backend\assets\editor;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class ReteAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [];

  


    public $js = [
        'public/libs/editor/rete/lodash.min.js',
        'public/libs/editor/rete/rete.min.js',
        'public/libs/editor/rete/vue.min.js',
        'public/libs/editor/rete/vue-render-plugin.min.js',
        'public/libs/editor/rete/connection-plugin.min.js',
        'public/libs/editor/rete/area-plugin.min.js',
        'public/libs/editor/rete/context-menu-plugin.min.js?',
        'public/libs/editor/rete/module-plugin.min.js',
        'public/libs/editor/rete/auto-arrange-plugin.min.js?',
     
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        $this->jsOptions['position'] =  \yii\web\View::POS_HEAD;


        parent::init();
    }

    
}

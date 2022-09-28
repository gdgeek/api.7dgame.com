<?php

namespace common\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class FontAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        './libs/fontawesome-free-5.11.2-web/css/all.css'
    ];
    public $js = [
    ];
    public $depends = [
    ];
    public function init() {
        $this->jsOptions['position'] =  \yii\web\View::POS_BEGIN;
        parent::init();
    }
}

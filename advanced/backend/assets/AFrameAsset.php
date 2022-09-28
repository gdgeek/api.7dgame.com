<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AFrameAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'public/libs/aframe/1.2.0/aframe.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
    public function init()
    {
        $this->jsOptions['position'] =  \yii\web\View::POS_BEGIN;
        parent::init();
    }
}

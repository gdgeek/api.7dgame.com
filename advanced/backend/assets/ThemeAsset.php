<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class ThemeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'public/template/pages/assets/css/socicon.css',
        'public/template/pages/assets/css/entypo.css',
        'public/template/pages/assets/css/theme.css',
        'https://fonts.googleapis.com/css?family=Rubik:300,400,400i,500',
    ];
    public $js = [
        'public/template/pages/assets/js/popper.min.js',
        'public/template/pages/assets/js/bootstrap.js',
        'public/template/pages/assets/js/aos.js',
        'public/template/pages/assets/js/flatpickr.min.js',
        'public/template/pages/assets/js/flickity.pkgd.min.js',
        'public/template/pages/assets/js/jarallax.min.js',
        'public/template/pages/assets/js/jarallax-video.min.js',
        'public/template/pages/assets/js/jarallax-element.min.js',
        'public/template/pages/assets/js/scrollMonitor.js',
        'public/template/pages/assets/js/jquery.smartWizard.min.js',
        'public/template/pages/assets/js/smooth-scroll.polyfills.min.js',
        'public/template/pages/assets/js/prism.js',
        'public/template/pages/assets/js/zoom.min.js',
        'public/template/pages/assets/js/theme.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}



<?php

namespace frontend\assets;

use yii\web\AssetBundle;

use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class ThemeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'pages/assets/css/socicon.css',
        'pages/assets/css/entypo.css',
        'pages/assets/css/theme.css?1',
        'https://fonts.googleapis.com/css?family=Rubik:300,400,400i,500',
    ];
    public $js = [
        'pages/assets/js/popper.min.js',
        'pages/assets/js/bootstrap.js',
        'pages/assets/js/aos.js',
        'pages/assets/js/flatpickr.min.js',
        'pages/assets/js/flickity.pkgd.min.js',
        'pages/assets/js/jarallax.min.js',
        'pages/assets/js/jarallax-video.min.js',
        'pages/assets/js/jarallax-element.min.js',
        'pages/assets/js/scrollMonitor.js',
        'pages/assets/js/jquery.smartWizard.min.js',
        'pages/assets/js/smooth-scroll.polyfills.min.js',
        'pages/assets/js/prism.js',
        'pages/assets/js/zoom.min.js',
        'pages/assets/js/theme.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
   
}



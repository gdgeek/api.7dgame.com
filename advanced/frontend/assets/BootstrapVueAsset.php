<?php

namespace frontend\assets;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Main frontend application asset bundle.
 */
class BootstrapVueAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';



    public $css = [
        //'libs/bootstrap/css/bootstrap.min.css',
        'libs/bootstrap-vue/bootstrap-vue.min.css',
    ];
    public $js = [
        'libs/vue/vue.min.js',
        'libs/bootstrap-vue/bootstrap-vue.min.js',
        'libs/axios/axios.min.js',
    ];



    /*
    public $js = [
      //  'libs/vue/vue.min.js',
       // 'libs/vue/vue-router.js',
       // 'libs/vue/axios.min.js',
       // 'libs/vue/vue-axios.min.js',

    ];*/
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}

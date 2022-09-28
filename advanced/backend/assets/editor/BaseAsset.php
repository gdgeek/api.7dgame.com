<?php

namespace backend\assets\editor;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class BaseAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'public/css/site.css',
        'public/libs/editor/css/style.css',
    ];

    function __construct()
    {
        $time = time();
        $this->js = [





            'public/libs/vcolorpicker/vcolorpicker.umd.min.js',
            'public/libs/editor/js/MrPP.js?' . $time,
            'public/libs/editor/js/sockets/SocketList.js?' . $time,
            'public/libs/editor/js/control/NumberControl.js?' . $time,
            'public/libs/editor/js/control/ColorPickerControl.js?' . $time,
            'public/libs/editor/js/control/StringControl.js?' . $time,
            'public/libs/editor/js/control/LinkControl.js?' . $time,
            'public/libs/editor/js/control/ButtonControl.js?' . $time,
            'public/libs/editor/js/control/Vector3Control.js?' . $time,
            'public/libs/editor/js/control/Vector2Control.js?' . $time,
            'public/libs/editor/js/control/SelectControl.js?' . $time,
            'public/libs/editor/js/control/EditorControl.js?' . $time,
            'public/libs/editor/js/control/BoolControl.js?' . $time,
            'public/libs/editor/js/control/KeyBoolControl.js?' . $time,
            'public/libs/editor/js/control/KeyStringControl.js?' . $time,
        ];
    }

    public $js = [];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}

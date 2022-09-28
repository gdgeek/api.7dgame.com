<?php
return [
    'language' => 'zh-CN',
    'sourceLanguage' => 'en-US',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@mrpp' => 'https://mrpp.com',
        //'@wfront'  => 'https://mrpp.com/#'
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'timeZone' => 'Asia/Shanghai',
    'name' => '混合现实编程平台（MrPP.com）',

    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/editor' => 'editor.php',
                        'app/system' => 'system.php',
                        'app/error' => 'error.php',
                        'app/site' => 'site.php',
                    ],
                ],
            ],
        ],
        'fileStorage' => [
            'class' => 'yii2tech\filestorage\local\Storage',
            'basePath' => '@webroot/files',
            'baseUrl' => '@web/files',
            'dirPermission' => 0775,
            'filePermission' => 0755,
            'buckets' => [
                'tempFiles' => [
                    'baseSubPath' => 'temp',
                    'fileSubDirTemplate' => '{^name}/{^^name}',
                ],
                'imageFiles' => [
                    'baseSubPath' => 'image',
                    'fileSubDirTemplate' => '{ext}/{^name}/{^^name}',
                ],
            ],
        ],

    ],

];

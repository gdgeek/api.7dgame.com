<?php
return [
    'language' => 'zh-CN',
    'sourceLanguage' => 'en-US',
    'aliases' => [
        '@bower' => '@vendor/yidas/yii2-bower-asset/bower',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'timeZone' => 'Asia/Shanghai',
    'name' => '元宇宙实景编程平台（MrPP.com）',
    
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
        'jwt' => [
            'class' => \bizley\jwt\Jwt::class,
            'signer' => \bizley\jwt\Jwt::ES256,
            'signingKey' => [
                'key' =>  getenv('JWT_KEY'), // path to your PRIVATE key, you can start the path with @ to indicate this is a Yii alias
                'passphrase' => '', // omit it if you are not adding any passphrase
                'method' => \bizley\jwt\Jwt::METHOD_FILE,
            ],
            'validationConstraints'=> static function (\bizley\jwt\Jwt $jwt) {
                $config = $jwt->getConfiguration();
                return [
                    new \Lcobucci\JWT\Validation\Constraint\SignedWith($config->signer(), $config->verificationKey()),
                    new \Lcobucci\JWT\Validation\Constraint\LooseValidAt(
                        new \Lcobucci\Clock\SystemClock(new \DateTimeZone(\Yii::$app->timeZone)),
                        new \DateInterval('PT10S')
                    ),
                ];
            }
        ],
        
    ],
    
    
];

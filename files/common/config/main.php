<?php
return [
    'language' => 'zh-CN',
    'sourceLanguage' => 'zh-CN',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'timeZone' => 'Asia/Shanghai',
    'name' => 'AR创作平台',
    
    'components' => [
        'redis' => [
            'class' => 'yii\\redis\\Connection',
            'hostname' => getenv('REDIS_HOST') ?: 'localhost',
            'port' => getenv('REDIS_PORT') ?: 6379,
            'database' => getenv('REDIS_DB') ?: 0,
            'password' => getenv('REDIS_PASSWORD') ?: null,
        ],
        'cache' => [
            'class' => 'yii\\redis\\Cache',
            'redis' => 'redis',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'sourceLanguage' => 'zh-CN',
                    'enableCaching' => true,
                    'cachingDuration' => 3600,
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
            'signer' => \bizley\jwt\Jwt::HS256,
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

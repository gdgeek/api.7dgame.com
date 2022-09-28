<?php
return [
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=db;dbname=yii2advanced',
            'username' => 'root',
            'password' => 'Setup2021',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
        'jwt' => [
            'class' => \sizeg\jwt\Jwt::class,
            'key' => 'C0M.test.cime.',
            'jwtValidationData' => [
                'class' => \common\components\JwtValidationData::class,
                // configure leeway
                'leeway' => 20,
            ],
        ],/*
        'store' => [
            'class' => \common\components\Store::class,
            'secretId' => 'secretId',
            'secretKey' => 'secretKey',
            'bucket' => 'bucket',
            'region' => 'region',
        ],
        'wechat' => [
            'class' => \common\components\WeChat::class,
            'app_id' => 'app_id',
            'secret' => 'secrt',
        ],*/
    ],
];

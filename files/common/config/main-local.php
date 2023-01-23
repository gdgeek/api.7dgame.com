<?php
return [
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=mrpp-db;dbname=yii2advanced',
            'username' => 'root',
            'password' => getenv('MYSQL_ROOT_PASSWORD'),
            'charset' => 'utf8',
            'enableSchemaCache' => true,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
// send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'jwt' => [
            'class' => \sizeg\jwt\Jwt::class,
            'key' => getenv('JWT_KEY'),
            'jwtValidationData' => [
                'class' => \common\components\JwtValidationData::class,
// configure leeway
                'leeway' => 20,
            ],
        ],
        'store' => [
            'class' => \common\components\Store::class,
            'secretId' => getenv('STORE_SECRET_ID'),
            'secretKey' => getenv('STORE_SECRET_KEY'),
            'bucket' => getenv('STORE_BUKET'),
            'region' => getenv('STORE_REGION'),
        ],
        'secret' => [
            'class' => \common\components\Secret::class,
            'secretId' => getenv('SECRET_ID'),
            'secretKey' => getenv('SECRET_KEY'),
        ],
        'wechat' => [
            'class' => \common\components\WeChat::class,
            'app_id' => getenv('WECHAT_APP_ID'),
            'secret' => getenv('WECHAT_APP_SECRET'),
        ],
    ],
];

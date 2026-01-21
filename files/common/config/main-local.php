<?php
return [
    'components' => [
        'secret' => [
            'class' => \common\components\Secret::class,
            'id' => getenv('SECRET_ID'),
            'key' => getenv('SECRET_KEY'),
            'cloud' => [
                'public' => [
                    'bucket' => getenv('COS_BUCKETS_PUBLIC_BUCKET'),
                    'region' => getenv('COS_BUCKETS_PUBLIC_REGION'),
                ],
                'private' => [
                    'bucket' => getenv('COS_BUCKETS_PRIVATE_BUCKET'),
                    'region' => getenv('COS_BUCKETS_PRIVATE_REGION'),
                ],
            ],
        ],
        'db' => [
            'class' => 'common\components\CynosDbConnection',
            //'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . getenv('MYSQL_HOST') . ';dbname=' . getenv('MYSQL_DB'),
            'username' => getenv('MYSQL_USERNAME'),
            'password' => getenv(name: 'MYSQL_PASSWORD'),
            'charset' => 'utf8',
        ],

        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOST'),
            'port' => getenv('REDIS_PORT'),
            'database' => getenv('REDIS_DB'),
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.exmail.qq.com',
                'username' => getenv('MAILER_USERNAME'),
                'password' => getenv('MAILER_PASSWORD'),
                'port' => '25',
                'encryption' => 'tls',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => [getenv('MAILER_USERNAME') => getenv('MAILER_USERNAME')],
            ],
        ],
    ],
];

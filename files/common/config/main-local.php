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
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . getenv('MYSQL_HOST') . ';dbname=yii2advanced',
            'username' => 'root',
            'password' => getenv('MYSQL_ROOT_PASSWORD'),
            'charset' => 'utf8',
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
                'from' => ['dirui@mrpp.com' => 'dirui'],
            ],
        ],
        

    ],
];

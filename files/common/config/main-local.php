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
        'wechat' => [
            'class' => \common\components\WeChat::class,
            'app_id' => getenv('WECHAT_APP_ID') ?: getenv('WECHAT_APPID') ?: null,
            'secret' => getenv('WECHAT_SECRET') ?: null,
            'token' => getenv('WECHAT_TOKEN') ?: null,
            'mch_id' => getenv('WECHAT_MCH_ID') ?: null,
            'private_key' => getenv('WECHAT_PAY_PRIVATE_KEY') ?: null,
            'certificate' => getenv('WECHAT_PAY_CERTIFICATE') ?: null,
            'secret_key' => getenv('WECHAT_PAY_SECRET_KEY') ?: null,
            'pay_notify_url' => getenv('WECHAT_PAY_NOTIFY_URL') ?: null,
            'pay_url' => getenv('WECHAT_PAY_URL') ?: null,
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
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => 'smtp',
                'host' => 'smtp.exmail.qq.com',
                'username' => getenv('MAILER_USERNAME') ?: null,
                'password' => getenv('MAILER_PASSWORD') ?: null, // 注意：这里需要使用授权码，不是邮箱密码
                'port' => 465,
                'encryption' => 'ssl',
            ],
        ],
    ],
];

<?php
return [
    'components' => [
        'store' => [
            'class' => \common\components\Store::class,
            'secretId' => getenv('STORE_SECRET_ID'),
            'secretKey' => getenv('STORE_SECRET_KEY'),
            'raw' => [
                'bucket' => getenv('COS_RAW_BUKET'),
                'region' => getenv('COS_RAW_REGION'),
            ],
            'release' => [
                'bucket' => getenv('COS_RELEASE_BUKET'),
                'region' => getenv('COS_RELEASE_REGION'),
            ],
            'store' => [
                'bucket' => getenv('COS_STORE_BUKET'),
                'region' => getenv('COS_STORE_REGION'),
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
        'jwt' => [
            'class' => \sizeg\jwt\Jwt::class,
            'key' => getenv('JWT_KEY'),
            'jwtValidationData' => \common\components\JwtValidationData::class,
        ],
        'wechat' => [
            'class' => \common\components\WeChat::class,
            'app_id' => getenv('WECHAT_APP_ID'),
            'secret' => getenv('WECHAT_SECRET'),
            'token' => getenv('WECHAT_TOKEN'),
        ],
        'pay' => [
            'class' => \common\components\WeChatPay::class,
            'mch_id' => getenv('PAY_MCH_ID'),
            'private_key' => __DIR__ . '/certs/apiclient_key.pem',
            'certificate' => __DIR__ . '/certs/apiclient_cert.pem',
            'certificate_serial_no' => getenv('PAY_SERIAL_NO'),
            'http' => [
                'base_uri' => 'https://api.mch.weixin.qq.com/',
            ],
            'secret_key' => getenv('PAY_SECRET_KEY'),

        ],
    ],
];

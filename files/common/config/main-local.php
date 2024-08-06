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
        'jwt' => [
            'class' => \bizley\jwt\Jwt::class,
            'signer' => \bizley\jwt\Jwt::ES256,
            'signingKey' => [
                'key' =>  getenv('JWT_KEY'), // path to your PRIVATE key, you can start the path with @ to indicate this is a Yii alias
                'passphrase' => '', // omit it if you are not adding any passphrase
                'method' => \bizley\jwt\Jwt::METHOD_FILE,
            ],
              'verifyingKey' => [ // required for asymmetric keys
                'key' =>  getenv('JWT_PUBLIC_KEY'), // path to your PUBLIC key, you can start the path with @ to indicate this is a Yii alias
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
/*
        'jwt_parameter' => [
            'class' => \common\components\JwtParameter::class,
            'issuer' => getenv('JWT_ISSUER'),
            'audience' => getenv('JWT_AUDIENCE'),
            'id' => getenv('JWT_ID'),
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
        ],*/
    ],
];

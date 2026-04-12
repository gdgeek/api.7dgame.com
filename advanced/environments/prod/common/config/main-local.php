<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . (getenv('MYSQL_HOST') ?: 'localhost') . ';dbname=' . (getenv('MYSQL_DB') ?: 'yii2advanced'),
            'username' => getenv('MYSQL_USERNAME') ?: 'root',
            'password' => getenv('MYSQL_PASSWORD') ?: '',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => 'smtp',
                'host' => 'smtp.exmail.qq.com',
                'username' => getenv('MAILER_USERNAME') ?: null,
                'password' => getenv('MAILER_PASSWORD') ?: null,
                'port' => 465,
                'encryption' => 'ssl',
            ],
        ],
    ],
];

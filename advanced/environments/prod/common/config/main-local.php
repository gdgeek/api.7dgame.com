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
        'pluginDb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . (getenv('PLUGIN_MYSQL_HOST') ?: getenv('MYSQL_HOST') ?: 'localhost')
                     . ';dbname=' . (getenv('PLUGIN_MYSQL_DB') ?: 'bujiaban_plugin'),
            'username' => getenv('PLUGIN_MYSQL_USERNAME') ?: getenv('MYSQL_USERNAME') ?: 'root',
            'password' => getenv('PLUGIN_MYSQL_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '',
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

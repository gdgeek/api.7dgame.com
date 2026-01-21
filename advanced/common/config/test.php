<?php
return [
    'id' => 'app-common-tests',
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=yii2_advanced_test',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
    ],
];

<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
        'task51-migrate' => [
            'class' => \console\controllers\Task51MigrateController::class,
            'db' => 'task51CoordinatorDb',
            'migrationPath' => '@console/migrations',
            'migrationTable' => '{{%migration}}',
        ],
    ],
    'components' => [
        // Task 5.1 DDL must not use the application's CynosDB connection or
        // per-statement retry command. Migration invocation explicitly selects
        // this lazy, standard Yii connection against the same MySQL authority.
        'task51CoordinatorDb' => [
            'class' => \yii\db\Connection::class,
            'commandClass' => \yii\db\Command::class,
            'dsn' => 'mysql:host=' . getenv('MYSQL_HOST') . ';dbname=' . getenv('MYSQL_DB'),
            'username' => getenv('MYSQL_USERNAME'),
            'password' => getenv('MYSQL_PASSWORD'),
            'charset' => 'utf8mb4',
            'enableSlaves' => false,
            'attributes' => [\PDO::ATTR_TIMEOUT => 5],
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];

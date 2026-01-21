<?php
/**
 * Global test bootstrap file for Codeception
 * This file is loaded before running tests across all test suites
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Set up common aliases
Yii::setAlias('@common', __DIR__ . '/common');
Yii::setAlias('@frontend', __DIR__ . '/frontend');
Yii::setAlias('@backend', __DIR__ . '/backend');
Yii::setAlias('@console', __DIR__ . '/console');
Yii::setAlias('@api', __DIR__ . '/api');
Yii::setAlias('@tests', __DIR__ . '/tests');

// Create a minimal application for tests
new \yii\console\Application([
    'id' => 'testapp',
    'basePath' => __DIR__,
    'vendorPath' => __DIR__ . '/vendor',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;dbname=yii2_advanced_test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
        ],
        'cache' => [
            'class' => 'yii\caching\ArrayCache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'jwt' => [
            'class' => 'bizley\jwt\Jwt',
            'signer' => 'HS256',
            'signingKey' => 'secure-signing-key-should-be-at-least-256-bits-long-for-hs256',
        ],
    ],
]);

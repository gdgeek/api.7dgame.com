<?php
/**
 * Integration test bootstrap file
 */

// Load the main test bootstrap
require_once __DIR__ . '/../_bootstrap.php';

// Load configurations
$commonConfig = require __DIR__ . '/../../common/config/main.php';
$commonLocalConfig = file_exists(__DIR__ . '/../../common/config/main-local.php') 
    ? require __DIR__ . '/../../common/config/main-local.php' 
    : [];
$testConfig = require __DIR__ . '/../../common/config/test.php';

// Merge configurations
$config = \yii\helpers\ArrayHelper::merge(
    $commonConfig,
    $commonLocalConfig,
    $testConfig,
    [
        'id' => 'integration-tests',
        'basePath' => dirname(__DIR__, 2) . '/api',
        'components' => [
            // Configure Redis for testing
            'redis' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 1, // Use database 1 for testing
            ],
            // Ensure mailer uses file transport for testing
            'mailer' => [
                'class' => 'yii\swiftmailer\Mailer',
                'viewPath' => '@common/mail',
                'useFileTransport' => true,
            ],
        ],
    ]
);

// Create application instance for integration tests
$application = new \yii\web\Application($config);

Yii::$app = $application;

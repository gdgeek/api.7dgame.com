<?php
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

// PHP 8.5: 抑制第三方库（如腾讯云SDK）的 deprecated 警告
error_reporting(E_ALL & ~E_DEPRECATED);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

(new yii\web\Application($config))->run();

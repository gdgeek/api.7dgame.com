<?php
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

// Default OFF: admission runs before Composer/Yii/config and any authentication/session.
if (!in_array(strtolower(trim((string) getenv('IDENTITY_DEV_ADMISSION_ENABLED'))), ['', '0', 'false', 'off'], true)) {
    require_once __DIR__ . '/../../common/components/admission/HttpAdmission.php';
    if (!\common\components\admission\HttpAdmission::isRunning()) {
        \common\components\admission\HttpAdmission::runEntry(__FILE__);
        return;
    }
}

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

if (class_exists(\common\components\admission\HttpAdmission::class, false) && \common\components\admission\HttpAdmission::isRunning()) {
    $admissionApplicationClass = \common\components\admission\HttpAdmission::applicationClass();
    (new $admissionApplicationClass($config))->run();
} else {
    (new yii\web\Application($config))->run();
}

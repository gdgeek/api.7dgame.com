<?php
$yiiDebugValue = strtolower(trim((string)getenv('YII_DEBUG')));
$yiiDebugTrueValues = ['1', 'true', 'yes', 'on'];
$yiiDebugFalseValues = ['', '0', 'false', 'no', 'off'];
$yiiDebugIsValid = in_array($yiiDebugValue, $yiiDebugTrueValues, true)
    || in_array($yiiDebugValue, $yiiDebugFalseValues, true);
$yiiDebug = in_array($yiiDebugValue, $yiiDebugTrueValues, true);
$yiiEnvironmentValue = strtolower(trim((string)getenv('YII_ENV')));
$yiiEnvironmentIsValid = $yiiEnvironmentValue === ''
    || in_array($yiiEnvironmentValue, ['prod', 'dev', 'test'], true);
$yiiEnvironment = $yiiEnvironmentValue === '' ? 'prod' : $yiiEnvironmentValue;

// Production-safe defaults. If Task 5.1 is accidentally enabled in a debug
// runtime, stop before Yii/debug RequestPanel can read or persist headers/body.
$task51EnabledValue = strtolower(trim((string)getenv('TASK51_STAGE_B_COORDINATOR_ENABLED')));
$task51EnabledTrueValues = ['1', 'true', 'yes', 'on'];
$task51EnabledFalseValues = ['', '0', 'false', 'no', 'off'];
$task51EnabledIsValid = in_array($task51EnabledValue, $task51EnabledTrueValues, true)
    || in_array($task51EnabledValue, $task51EnabledFalseValues, true);
$task51Enabled = in_array($task51EnabledValue, $task51EnabledTrueValues, true);
$task51SensitiveConfigurationPresent = $task51Enabled
    || !$task51EnabledIsValid
    || trim((string)getenv('TASK51_STAGE_B_COORDINATOR_PUBLIC_ORIGIN')) !== ''
    || trim((string)getenv('TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA')) !== ''
    || trim((string)getenv('TASK51_STAGE_B_INTERNAL_TOKEN')) !== '';
$task51RequestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$task51Request = is_string($task51RequestPath)
    && ($task51RequestPath === '/v1/task51/stage-b'
        || str_starts_with($task51RequestPath, '/v1/task51/stage-b/'));
$task51NonCanonicalFrontControllerRequest = is_string($task51RequestPath)
    && ($task51RequestPath === '/index.php/v1/task51/stage-b'
        || str_starts_with($task51RequestPath, '/index.php/v1/task51/stage-b/'));

// The coordinator has one canonical public path. Reject the front-controller
// alias before Yii so it cannot gain a separate debug/logging or edge-policy
// surface. Apache applies the same request-body limit to both spellings.
if ($task51NonCanonicalFrontControllerRequest) {
    http_response_code(404);
    header('Cache-Control: no-store, private, max-age=0');
    exit;
}

defined('YII_DEBUG') or define('YII_DEBUG', $yiiDebug);
defined('YII_ENV') or define('YII_ENV', $yiiEnvironment);
if (($task51SensitiveConfigurationPresent || $task51Request)
    && (!$yiiDebugIsValid || !$yiiEnvironmentIsValid || $yiiDebug || $yiiEnvironment !== 'prod'
        || YII_DEBUG !== false || YII_ENV !== 'prod')) {
    http_response_code(404);
    header('Cache-Control: no-store, private, max-age=0');
    exit;
}

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

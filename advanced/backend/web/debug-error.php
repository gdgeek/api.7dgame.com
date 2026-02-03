<?php
// 临时调试页面 - 用完删除
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../../common/config/main.php',
    require __DIR__ . '/../../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

// 显示最近的错误日志
$logFile = __DIR__ . '/../runtime/logs/app.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recent = array_slice($lines, -50);
    echo "<h2>Recent Logs:</h2><pre>";
    echo htmlspecialchars(implode('', $recent));
    echo "</pre>";
} else {
    echo "Log file not found: $logFile";
}

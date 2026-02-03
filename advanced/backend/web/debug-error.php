<?php
// 临时调试页面
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 显示最近的错误日志
$logFile = __DIR__ . '/../runtime/logs/app.log';
echo "<h2>Log file: $logFile</h2>";
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recent = array_slice($lines, -100);
    echo "<h2>Recent Logs:</h2><pre>";
    echo htmlspecialchars(implode('', $recent));
    echo "</pre>";
} else {
    echo "Log file not found<br>";
}

// 显示 mdm-admin 版本
$composerLock = __DIR__ . '/../../composer.lock';
if (file_exists($composerLock)) {
    $lock = json_decode(file_get_contents($composerLock), true);
    foreach ($lock['packages'] as $pkg) {
        if ($pkg['name'] === 'mdmsoft/yii2-admin') {
            echo "<h3>mdm-admin version: " . $pkg['version'] . "</h3>";
            break;
        }
    }
}

echo "<h3>PHP Version: " . phpversion() . "</h3>";

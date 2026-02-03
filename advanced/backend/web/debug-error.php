<?php
// 临时调试页面 - 用完删除
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
    // 列出 runtime 目录
    $runtimeDir = __DIR__ . '/../runtime';
    if (is_dir($runtimeDir)) {
        echo "<h3>Runtime dir contents:</h3><pre>";
        print_r(scandir($runtimeDir));
        echo "</pre>";
        $logsDir = $runtimeDir . '/logs';
        if (is_dir($logsDir)) {
            echo "<h3>Logs dir contents:</h3><pre>";
            print_r(scandir($logsDir));
            echo "</pre>";
        }
    }
}

<?php
// 临时调试页面 - 用完删除
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 直接加载应用并访问 admin/permission/index
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../common/config/bootstrap.php';
require __DIR__ . '/../../backend/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../common/config/main.php',
    require __DIR__ . '/../common/config/main-local.php',
    require __DIR__ . '/../../backend/config/main.php',
    require __DIR__ . '/../../backend/config/main-local.php'
);

echo "<h2>PHP Version: " . phpversion() . "</h2>";
echo "<h2>Testing mdm-admin module...</h2>";

try {
    // 检查 mdm-admin 版本
    $composerLock = __DIR__ . '/../composer.lock';
    if (file_exists($composerLock)) {
        $lock = json_decode(file_get_contents($composerLock), true);
        foreach ($lock['packages'] as $pkg) {
            if ($pkg['name'] === 'mdmsoft/yii2-admin') {
                echo "mdm-admin version: " . $pkg['version'] . "<br>";
                break;
            }
        }
    }
} catch (\Throwable $e) {
    echo "<pre>Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
}

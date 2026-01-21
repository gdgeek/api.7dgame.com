<?php
/**
 * 测试健康检查和版本号接口
 * 
 * 使用方法:
 * php tests/manual/test_health_version.php
 * 
 * 或通过浏览器访问:
 * http://your-domain/site/health
 * http://your-domain/site/version
 */

// 定义应用类型
defined('YII_ENV') or define('YII_ENV', 'dev');
defined('YII_DEBUG') or define('YII_DEBUG', true);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../../api/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../../api/config/main-local.php'
);

$application = new yii\web\Application($config);

echo "=== 测试健康检查接口 ===\n";
try {
    $controller = new \api\controllers\SiteController('site', $application);
    $health = $controller->actionHealth();
    echo "健康检查结果:\n";
    print_r($health);
    echo "\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

echo "\n=== 测试版本号接口 ===\n";
try {
    $controller = new \api\controllers\SiteController('site', $application);
    $version = $controller->actionVersion();
    echo "版本信息:\n";
    print_r($version);
    echo "\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

echo "\n=== 接口访问地址 ===\n";
echo "健康检查: GET /site/health\n";
echo "版本信息: GET /site/version\n";

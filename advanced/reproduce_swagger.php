<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
// require __DIR__ . '/common/config/bootstrap.php'; // This might be gitignored too or fail

Yii::setAlias('@api', __DIR__ . '/api');
Yii::setAlias('@common', __DIR__ . '/common');

use OpenApi\Generator;

require __DIR__ . '/test_swagger_file.php';
$files = [
    __DIR__ . '/test_swagger_file.php',
];

// Set error handler to catch warnings
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

foreach ($files as $file) {
    echo "Scanning $file ...\n";
    try {
        $openapi = Generator::scan([$file]);
        echo "OK\n";
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "Scanning all ...\n";
try {
    $openapi = Generator::scan($files);
    echo "All OK\n";
} catch (\Throwable $e) {
    echo "Error scanning all: " . $e->getMessage() . "\n";
}

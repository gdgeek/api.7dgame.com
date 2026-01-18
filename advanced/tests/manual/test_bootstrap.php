<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', __DIR__);

require_once __DIR__ .  '/vendor/autoload.php';
require_once __DIR__ .  '/vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('@common', __DIR__ . '/common');
Yii::setAlias('@api', __DIR__ . '/api');
Yii::setAlias('@backend', __DIR__ . '/backend');
Yii::setAlias('@console', __DIR__ . '/console');

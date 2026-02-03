<?php
// 覆盖 mdm-admin 的 AuthItem 类以修复 PHP 8.1+ 兼容性
Yii::$classMap['mdm\admin\models\searchs\AuthItem'] = dirname(dirname(dirname(__DIR__))) . '/common/overrides/mdm-admin/AuthItem.php';

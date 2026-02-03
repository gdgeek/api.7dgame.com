<?php
// 覆盖 mdm-admin 的 AuthItem 类以修复 PHP 8.1+ 兼容性
// 路径: files/backend/config/bootstrap.php -> advanced/common/overrides/mdm-admin/AuthItem.php
Yii::$classMap['mdm\admin\models\searchs\AuthItem'] = dirname(dirname(dirname(__DIR__))) . '/advanced/common/overrides/mdm-admin/AuthItem.php';

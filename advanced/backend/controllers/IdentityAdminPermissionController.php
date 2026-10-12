<?php

namespace backend\controllers;

require_once __DIR__ . '/IdentityAdminRoleController.php';

class IdentityAdminPermissionController extends \mdm\admin\controllers\PermissionController
{
    use IdentityAdminItemSearch;
}

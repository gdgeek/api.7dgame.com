<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'manager',
    'name'=>'AR创作平台',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log', \common\components\IdentityBoundaryBootstrap::class],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'admin' => [
            'class' => 'mdm\admin\Module',
            'controllerMap' => [
                'user' => \backend\controllers\IdentityAdminUserController::class,
                'assignment' => \backend\controllers\IdentityAdminAssignmentController::class,
                'role' => \backend\controllers\IdentityAdminRoleController::class,
                'permission' => \backend\controllers\IdentityAdminPermissionController::class,
                'rule' => \backend\controllers\IdentityAdminRuleController::class,
            ],
            'layout' => '@app/views/layouts/main.php', 
        ],
        'reporter' => [
            'class' => 'backend\modules\Reporter', 
        ], 
        'lua' => [ 
            'class' => 'backend\modules\Lua', 
        ],
        
    ],
    'components' => [
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/'=> 'site/index'
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'class' => \common\components\IdentityBackendUser::class,
            'identityClass' => 'api\modules\v1\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'common\components\security\SafeFileTarget',
                    'levels' => ['info', 'error', 'warning'],
                ],
                [
                    'class' => 'common\components\security\SafeFileTarget',
                    'levels' =>  ['info', 'error', 'warning'],
                    'categories' => ['mrpp'],
                    'logFile' => '@app/runtime/logs/mrpp.log',
                ],
            ],
        ],
        'authManager' => [
            'class' => \common\components\IdentityDbManager::class,
        ],
        
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        
        
    ],
    
    
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [   
            'site/*', // add or remove allowed actions to this list
            'wechat/*',
            'file/file',
            ]
        ],
        'params' => $params,
    ];

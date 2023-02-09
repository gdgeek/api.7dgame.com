<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'manager',
	'name'=>'MrPP.com',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [
		'admin' => [
				'class' => 'mdm\admin\Module',
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
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
                    
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' =>  ['info', 'error', 'warning'],
                    'categories' => ['mrpp'],
                    'logFile' => '@app/runtime/logs/mrpp.log',
                ],
            ],
        ],
		'authManager' => [
            'class' => 'yii\rbac\DbManager', 
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

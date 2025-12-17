<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'restful',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
        ],
        'a1' => [
            'class' => 'api\modules\a1\Module',
        ],
        'vp' => [
            'class' => 'api\modules\vp\Module',
        ],
    ],
    'as cors' => [
        'class' => \yii\filters\Cors::className(),
        'cors' => [
            'Origin' => ['*'],
            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
            'Access-Control-Request-Headers' => ['*'],
            'Access-Control-Allow-Credentials' => null,
            'Access-Control-Max-Age' => 86400,
            'Access-Control-Expose-Headers' => [
                'X-Pagination-Total-Count',
                'X-Pagination-Page-Count',
                'X-Pagination-Current-Page',
                'X-Pagination-Per-Page',
            ],
        ],
    ],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-api',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'user' => [
            'identityClass' => 'api\modules\v1\models\User',
            'enableAutoLogin' => true,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'player' => [
            'class' => 'api\modules\vp\helper\Player',
        ],
        'log' => [
            'traceLevel' => 3, //YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['mrpp'],
                    'logFile' => '@api/runtime/logs/mrpp.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'pattern' => 'apple-app-site-association',
                    'route' => 'site/apple-app-site-association',
                    'suffix' => ''
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'vp/site',
                    'extraPatterns' => [
                        'GET check' => 'check',
                        'POST check' => 'check',
                        'GET log' => 'log',
                        'GET test' => 'test',
                        'GET token' => 'token',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'vp/oauth2',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST apple-id' => 'apple-id',
                        'POST binding' => 'binding',
                        'GET test' => 'test',
                        'POST login' => 'login',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'vp/guide',
                    'extraPatterns' => [
                        'GET verse' => 'verse',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'vp/map',
                    'extraPatterns' => [
                        'GET page' => 'page',
                        'GET setup' => 'setup',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'vp/level',
                    'extraPatterns' => [
                        'GET record' => 'record',
                        'POST record' => 'record',
                        'GET log' => 'log',
                        'GET stars' => 'stars',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/tencent-cloud',
                    'extraPatterns' => [
                        'GET token' => 'token',
                        'GET store' => 'store',
                        'GET cloud' => 'cloud',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/user',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'PUT update' => 'update',
                        'GET get-data' => 'info',
                        'GET info' => 'info',
                        'GET creation' => 'creation',
                    ],
                ], 
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/phototype',
                    'pluralize' => true,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/wechat',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST register' => 'register',
                        'POST login' => 'login',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/auth',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'POST refresh' => 'refresh',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'site',
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'OPTIONS login' => 'login',
                        'POST signup' => 'signup',
                        'OPTIONS signup' => 'signup',
                        'GET password-reset-token' => 'password-reset-token',
                        'GET verify-email' => 'verify-email',
                        'GET binded-email' => 'binded-email',
                        'GET param' => 'param',
                        'GET information' => 'information',
                        'POST resend-verification-email' => 'resend-verification-email',
                        'OPTIONS resend-verification-email' => 'resend-verification-email',
                        'POST request-password-reset' => 'request-password-reset',
                        'OPTIONS request-password-reset' => 'request-password-reset',
                        'POST reset-password' => 'reset-password',
                        'OPTIONS reset-password' => 'reset-password',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'server',
                    'extraPatterns' => [
                        'GET user' => 'user',
                        'GET test' => 'test',
                        'GET token' => 'token',
                        'GET logout' => 'logout',
                        'GET menu' => 'menu',
                        'POST reset-password' => 'reset-password',
                        'POST bind-email' => 'bind-email',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/upload',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST file' => 'file',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/tools',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET user-linked' => 'user-linked',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['project', 'user', 'resource'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => [
                        'v1/verse-tags',
                    ],
                    'extraPatterns' => [
                        'POST remove' => 'remove',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => [
                        'v1/system',
                    ],
                    'extraPatterns' => [
                        'POST take-photo' => 'take-photo',
                        'GET verse' => 'verse',
                        'PUT verse-code' => 'verse-code',
                        'PUT meta-code' => 'meta-code',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => [
                        'v1/snapshot',
                    ],
                    'extraPatterns' => [
                        'POST take-photo' => 'take-photo',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'v1/meta',
                        'v1/prefab',
                        'v1/file',
                        'v1/resource',
                        'v1/tags',
                        'v1/token',
                        'v1/edu-school',
                        'v1/edu-teacher',
                        'v1/edu-class',
                        'v1/edu-student',
                        'v1/group',
                        'v1/group-verse',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => [
                        'v1/edu-school',
                        'v1/edu-teacher',
                        'v1/edu-class',
                        'v1/edu-student',
                        'v1/group',
                        'v1/group-verse',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/edu-school',
                    'extraPatterns' => [
                        'GET principal' => 'principal',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/edu-class',
                    'extraPatterns' => [
                        'GET by-teacher' => 'by-teacher',
                        'GET by-student' => 'by-student',
                        'GET {id}/groups' => 'get-groups',
                        'POST {id}/group' => 'create-group',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/group',
                    'extraPatterns' => [
                        'POST join' => 'join',
                        'POST {id}/join' => 'join',
                        'POST {id}/leave' => 'leave',
                        'GET {id}/verses' => 'get-verses',
                        'POST {id}/verse' => 'create-verse',
                        'DELETE {id}/verse/{verseId}' => 'delete-verse',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/edu-student',
                    'extraPatterns' => [
                        'GET me' => 'me',
                        'POST join' => 'join',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/edu-teacher',
                    'extraPatterns' => [
                        'GET me' => 'me',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/domain',
                    'extraPatterns' => [
                        'GET info' => 'info',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/verse',
                    'extraPatterns' => [
                        'GET public' => 'public',
                        'PUT code' => 'update-code'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/meta',
                    'extraPatterns' => [
                        'PUT code' => 'update-code',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/person',
                    'extraPatterns' => [
                        'PUT auth' => 'auth',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false,
                    'controller' => 'v1/site',
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'GET test' => 'test',
                        'POST test' => 'test',
                        'POST apple-id' => 'apple-id',
                        'POST apple-id-link' => 'apple-id-link',
                        'POST apple-id-create' => 'apple-id-create',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'a1/verse',
                    'extraPatterns' => [
                        'GET open' => 'open',
                        'GET release' => 'release',
                        'GET public' => 'public',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',

                    'pluralize' => false,
                    'controller' => [
                        'a1/game'
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/swagger',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET json' => 'json',
                        'GET yaml' => 'yaml',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/cyber-script',
                    'extraPatterns' => [
                        'GET find' => 'find',
                    ],
                ],

            ],

        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],

    ],

    /*,
    'as access' => [
    'class' => 'mdm\admin\components\AccessControl',
    'allowActions' => [
    'site/*',
    'resource/*',
    'resource/*',
    //    'v1/user/get-data'
    
    ],
    ],*/
    'params' => $params,
];

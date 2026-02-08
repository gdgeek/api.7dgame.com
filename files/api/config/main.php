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
        'healthService' => [
            'class' => 'common\components\HealthService',
        ],
        'rateLimiter' => [
            'class' => 'common\components\security\RateLimiter',
            'strategies' => [
                'ip' => ['limit' => 100, 'window' => 60],
                'user' => ['limit' => 1000, 'window' => 3600],
                'login' => ['limit' => 5, 'window' => 900],
            ],
        ],
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
        'log' => [
            'traceLevel' => 3, //YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'common\components\security\SafeFileTarget',
                    'levels' => ['info', 'error', 'warning'],
                ],
                [
                    'class' => 'common\components\security\SafeFileTarget',
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
                // 健康检查路由（无需认证）
                'GET health' => 'health/index',
                
                // Swagger API 文档路由
                'GET swagger' => 'swagger/index',
                'GET swagger/json-schema' => 'swagger/json-schema',
                
                [
                    'pattern' => 'apple-app-site-association',
                    'route' => 'site/apple-app-site-association',
                    'suffix' => ''
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
                    'extraPatterns' => [
                        'GET by-type' => 'by-type',
                    ],
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
                    'controller' => 'v1/email',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST send-verification' => 'send-verification',
                        'POST verify' => 'verify',
                        'GET test' => 'test',
                        'GET status' => 'status',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/password',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST request-reset' => 'request-reset',
                        'POST verify-token' => 'verify-token',
                        'POST reset' => 'reset',
                    ],
                ],
                /*
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
                ],*/
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
                /*
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['project', 'user', 'resource'],
                ],*/
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
                        'GET test' => 'test',
                        'POST upgrade' => 'upgrade',
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
                        // 'v1/edu-school',
                        // 'v1/edu-teacher',
                        //  'v1/edu-class',
                        //  'v1/edu-student',
                      //  'v1/group',
                        'v1/group-verse',
                    ],
                ],
                /*
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
                ],*/
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
                        'GET teacher-me' => 'teacher-me',
                        'POST {id}/teacher' => 'teacher',
                        'DELETE {id}/teacher' => 'remove-teacher',
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
                        'PUT code' => 'update-code',
                        'PUT {id}/code' => 'update-code',
                        'POST {id}/public' => 'add-public',
                        'DELETE {id}/public' => 'remove-public',
                        'POST {id}/tag' => 'add-tag',
                        'DELETE {id}/tag' => 'remove-tag',
                        'POST {id}/take-photo' => 'take-photo',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/meta',
                    'extraPatterns' => [
                        // 'PUT code' => 'update-code',
                        'PUT {id}/code' => 'update-code',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/person',
                    'extraPatterns' => [
                        'PUT auth' => 'auth',
                    ],
                ],
/*
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
                ],*/
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

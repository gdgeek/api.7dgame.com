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
        'e1' => [
            'class' => 'api\modules\e1\Module',
        ],
        'p1' => [
            'class' => 'api\modules\p1\Module',
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
                    'extraPatterns' => [
                        'PUT set-data' => 'set-data',
                        'GET get-data' => 'get-data',
                        'GET creation' => 'creation',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'wechat',
                    'extraPatterns' => [
                        'GET qrcode' => 'qrcode',
                        'GET test' => 'test',
                        'GET openid' => 'openid',
                        'OPTIONS binding' => 'binding',
                        'POST binding' => 'binding',
                        'OPTIONS signup' => 'signup',
                        'POST signup' => 'signup',
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
                    'extraPatterns' => [
                        'POST file' => 'file',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/like',
                    'extraPatterns' => [
                        'POST remove' => 'remove',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['project', 'user', 'resource'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'v1/meta',
                        'v1/prefab',
                        'v1/file',
                        'v1/verse-cyber',
                        'v1/message',
                        'v1/reply',
                        'v1/message-tags',
                        'v1/tags',
                        'v1/knight',
                        'v1/meta-knight',
                        'v1/verse',
                        'v1/verse-open',
                        'v1/verse-script',
                        'v1/event-input',
                        'v1/event-output',
                        'v1/event-link',
                        'v1/token',
                        'v1/space',
                        'v1/cyber',
                        'v1/vp-guide',
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
                    'controller' => 'v1/verse-share',
                    'extraPatterns' => [
                        'GET verses' => 'verses',
                        'PUT put' => 'put',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/verse-open',
                    'extraPatterns' => [
                        'GET verses' => 'verses',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/meta-resource',
                    'extraPatterns' => [
                        'GET resources' => 'resources',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/verse-meta',
                    'extraPatterns' => [
                        'GET metas' => 'metas',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/test',
                    'extraPatterns' => [
                        'GET file' => 'file',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/verse-space',
                    'extraPatterns' => [
                        'GET spaces' => 'spaces',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/verse-knight',
                    'extraPatterns' => [
                        'GET knights' => 'knights',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'p1/verse-open',
                        'p1/verse-publish',
                        'p1/verse-share',
                        /*
                        'e1/verse',
                        'e1/meta',
                        'e1/knight',*/
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['a1/verse','a1/vp-guide'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'p1/site',
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'GET logout' => 'logout',
                        'GET user' => 'user',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/cyber-script',
                    'extraPatterns' => [
                        'GET find' => 'find',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/local',
                    'extraPatterns' => [
                        'GET information' => 'information',
                        'GET param' => 'param',
                        'GET ready' => 'ready',
                        'GET init' => 'init',
                        'OPTIONS init' => 'init',
                        'POST init' => 'init',
                        'OPTIONS signup' => 'signup',
                        'POST signup' => 'signup',
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

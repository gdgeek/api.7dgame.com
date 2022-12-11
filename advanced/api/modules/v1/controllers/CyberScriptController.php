<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\CyberScript;
use yii\rest\ActiveController;

class CyberScriptController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\CyberScript';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
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
        ];

        /*
        // unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
        'class' => CompositeAuth::class,
        'authMethods' => [
        JwtHttpBearerAuth::class,
        ],
        'except' => ['options'],
        ];

        $behaviors['access'] = [
        'class' => AccessControl::class,
        ];
         */
        return $behaviors;
    }
    public function actionFind($cyber_id, $language = 'lua')
    {

        $script = CyberScript::findOne(['cyber_id' => $cyber_id, 'language' => $language]);
        return $script;
    }

}

<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Group;
use mdm\admin\components\AccessControl;
use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use Yii;

class GroupController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Group';

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
        
        return $behaviors;
    }
}

<?php
namespace api\modules\v1\controllers;
use Yii;
use yii\helpers\HtmlPurifier;
use yii\web\BadRequestHttpException;
use yii\filters\auth\CompositeAuth;

use yii\rest\ActiveController;

use mdm\admin\components\AccessControl;

use api\modules\v1\models\SpaceSearch;
use sizeg\jwt\JwtHttpBearerAuth;
class VpGuideController extends ActiveController
{

    public $modelClass = 'api\modules\v1\models\VpGuide';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

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

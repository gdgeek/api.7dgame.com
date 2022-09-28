<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\Token;
use yii\rest\ActiveController;

class TokenController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Token';
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET'],
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

        return $behaviors;
    }
    public function actions()
    {

        return [];
    }

    public function actionIndex($name = "access_token")
    {
        switch ($name) {
            case "access_token":
                $token = Token::accessToken();
            case "jsapi_ticket":
                $token = Token::jsapiToken();
        }
        $result = $token->attributes;

        $wechat = \Yii::$app->wechat;

        $result['appid'] = $wechat->app_id;

        return $result;
    }

}

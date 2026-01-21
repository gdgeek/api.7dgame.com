<?php
namespace api\modules\v1\controllers;

use api\modules\v1\models\Token;
use yii\rest\ActiveController;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Token",
 *     description="Token 管理接口"
 * )
 */
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

    /**
     * @OA\Get(
     *     path="/v1/token",
     *     summary="获取微信 Token",
     *     description="获取微信 access_token 或 jsapi_ticket",
     *     tags={"Token"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Token 类型",
     *         required=false,
     *         @OA\Schema(type="string", enum={"access_token", "jsapi_ticket"}, example="access_token")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token 信息",
     *         @OA\JsonContent(
     *             @OA\Property(property="appid", type="string", description="微信 AppID"),
     *             @OA\Property(property="token", type="string", description="Token 值"),
     *             @OA\Property(property="expires_at", type="integer", description="过期时间戳")
     *         )
     *     )
     * )
     */
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

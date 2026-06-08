<?php

namespace api\modules\v1\controllers;

use api\modules\v1\services\IdentityService;
use common\components\security\RateLimitBehavior;
use Yii;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="认证授权接口"
 * )
 */
class AuthController extends \yii\rest\Controller
{
    private ?IdentityService $identityService = null;

   // public $modelClass = 'app\modules\v1\models\Player';
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        $behaviors['rateLimiter'] = [
            'class' => RateLimitBehavior::class,
            'rateLimiter' => 'rateLimiter',
            'defaultStrategy' => 'ip',
            'actionStrategies' => [
                'login' => 'login',
            ],
        ];

        return $behaviors;
    }

    protected function identityService(): IdentityService
    {
        if ($this->identityService === null) {
            $this->identityService = new IdentityService();
        }

        return $this->identityService;
    }

    protected function requestContext(): array
    {
        return $this->identityService()->sessionService()->contextFromRequest(Yii::$app->request);
    }

    /**
     * 刷新访问令牌
     * 
     * @OA\Post(
     *     path="/v1/auth/refresh",
     *     summary="刷新访问令牌",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refreshToken"},
     *             @OA\Property(property="refreshToken", type="string", description="刷新令牌")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="刷新成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="refresh"),
     *             @OA\Property(property="token", type="string", description="新的访问令牌")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误")
     * )
     */
    public function actionRefresh()
    {
        $refreshToken = Yii::$app->request->post("refreshToken");
        $token = $this->identityService()->refresh($refreshToken, $this->requestContext());

        return ['success' => true, 'message' => "refresh", 'token'=> $token];

    }

    
    /**
     * 用户登录
     * 
     * @OA\Post(
     *     path="/v1/auth/login",
     *     summary="用户登录",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string", description="用户名", example="admin"),
     *             @OA\Property(property="password", type="string", description="密码", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登录成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="login"),
     *             @OA\Property(property="token", type="string", description="访问令牌")
     *         )
     *     ),
     *     @OA\Response(response=400, description="登录失败")
     * )
     */
    public function actionLogin()
    {
        $username = Yii::$app->request->post("username");
        $password = Yii::$app->request->post("password");
        $token = $this->identityService()->login($username, $password, $this->requestContext());

        return ['success' => true, 'message' => "login", 'token'=> $token];
    }

    public function actionLogout()
    {
        $refreshToken = Yii::$app->request->post("refreshToken");
        if (!is_string($refreshToken) || $refreshToken === '') {
            $refreshToken = null;
        }

        return [
            'success' => true,
            'message' => 'logout',
            'revoked' => $this->identityService()->logout($refreshToken),
        ];
    }

}

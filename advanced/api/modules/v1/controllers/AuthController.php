<?php

namespace api\modules\v1\controllers;

use api\modules\v1\services\IdentityService;
use common\components\security\RateLimitBehavior;
use api\modules\v1\services\DeviceSnCredential;
use api\modules\v1\services\DeviceSnService;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\TooManyRequestsHttpException;
use yii\filters\auth\CompositeAuth;
use bizley\jwt\JwtHttpBearerAuth;
use mdm\admin\components\AccessControl;
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
        $publicActions = ['options', 'login', 'refresh', 'logout', 'sn-activate', 'sn-login'];
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [JwtHttpBearerAuth::class],
            'except' => $publicActions,
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'allowActions' => $publicActions,
        ];

        $behaviors['rateLimiter'] = [
            'class' => RateLimitBehavior::class,
            'rateLimiter' => 'rateLimiter',
            'defaultStrategy' => 'ip',
            'actionStrategies' => [
                'login' => 'login',
            ],
            'except' => ['sn-activate', 'sn-login'],
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

    protected function verbs()
    {
        return [
            'login' => ['POST'],
            'refresh' => ['POST'],
            'logout' => ['POST', 'DELETE'],
            'sn-activate' => ['POST'],
            'sn-login' => ['POST'],
        ];
    }

    /**
     * @OA\Post(path="/v1/auth/sn-activate", summary="绑定设备 UUID 并激活 SN，返回标准登录令牌", tags={"Auth"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(required={"sn", "uuid"},
     *     @OA\Property(property="sn", type="string"), @OA\Property(property="uuid", type="string"))),
     *   @OA\Response(response=200, description="激活并登录成功"),
     *   @OA\Response(response=401, description="SN 或设备授权无效"),
     *   @OA\Response(response=429, description="请求过于频繁"))
     */
    public function actionSnActivate()
    {
        return $this->deviceLogin(true);
    }

    /**
     * @OA\Post(path="/v1/auth/sn-login", summary="已激活设备使用 UUID 与 SN 免密登录", tags={"Auth"},
     *   @OA\RequestBody(required=true, @OA\JsonContent(required={"sn", "uuid"},
     *     @OA\Property(property="sn", type="string"), @OA\Property(property="uuid", type="string"))),
     *   @OA\Response(response=200, description="登录成功"),
     *   @OA\Response(response=401, description="SN 或设备授权无效"),
     *   @OA\Response(response=429, description="请求过于频繁"))
     */
    public function actionSnLogin()
    {
        return $this->deviceLogin(false);
    }

    private function deviceLogin(bool $activate): array
    {
        $this->consumeDeviceLimit('ip', (string)Yii::$app->request->userIP);
        $sn = Yii::$app->request->post('sn');
        $uuid = Yii::$app->request->post('uuid');
        if (!is_string($sn) || !is_string($uuid) || strlen($sn) > 128 || trim($uuid) === '' || strlen($uuid) > 255) {
            throw new BadRequestHttpException('sn and uuid are required strings.');
        }
        $sn = DeviceSnCredential::normalize($sn);
        $uuid = DeviceSnService::normalizeUuid($uuid);
        $this->consumeDeviceLimit('sn', $sn);
        $this->consumeDeviceLimit('uuid', $uuid);
        $token = $this->identityService()->loginDeviceSn($sn, $uuid, $activate, $this->requestContext());

        return ['success' => true, 'message' => 'login', 'token' => $token];
    }

    protected function deviceRateLimiter()
    {
        return Yii::$app->get('deviceSnRateLimiter');
    }

    private function consumeDeviceLimit(string $strategy, string $value): void
    {
        try {
            $result = $this->deviceRateLimiter()->consume(hash('sha256', $value), $strategy);
        } catch (\Throwable $exception) {
            throw new HttpException(503, 'Device login is temporarily unavailable.');
        }
        if (!$result['allowed']) {
            Yii::$app->response->headers->set('Retry-After', (string)max(1, (int)$result['retry_after']));
            throw new TooManyRequestsHttpException('Too many device login attempts.');
        }
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

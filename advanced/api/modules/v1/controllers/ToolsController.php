<?php

namespace api\modules\v1\controllers;
use api\modules\v1\models\UserCreation;
use api\modules\v1\filters\LoginCodeReadinessBehavior;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use Yii;
use api\modules\v1\services\IdentityService;
use api\modules\v1\services\LoginCodeSettings;
use api\modules\v1\services\LoginCodeStore;
use common\components\security\RateLimitBehavior;
use yii\web\BadRequestHttpException;
use api\modules\v1\models\User;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Tools",
 *     description="工具类接口"
 * )
 */
class ToolsController extends \yii\rest\Controller
{
    private ?IdentityService $identityService = null;
    private ?LoginCodeStore $loginCodeStore = null;

    public function behaviors()
    {

        $behaviors = parent::behaviors();

      
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options'],
        ];
        // re-add authentication filter

        $behaviors['access'] = [
            'class' => AccessControl::class,
        ];

        $loginCodeSettings = LoginCodeSettings::fromApplication();
        if ($loginCodeSettings->usesRedis()) {
            $behaviors['loginCodeReadiness'] = [
                'class' => LoginCodeReadinessBehavior::class,
                'only' => ['user-linked', 'user-linked-status'],
            ];
        }

        // The dedicated limiter is enabled only once a Redis write mode is
        // explicitly selected. The safe database/database default does not
        // instantiate or depend on this Redis-backed component.
        if ($loginCodeSettings->writesRedis()) {
            $behaviors['loginCodeIssueRateLimiter'] = [
                'class' => RateLimitBehavior::class,
                'rateLimiter' => 'loginCodeIssueRateLimiter',
                'defaultStrategy' => 'user-linked-issue',
                'only' => ['user-linked'],
                'atomicConsume' => true,
                'telemetrySource' => 'main-api-issue',
            ];
        }

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

    protected function loginCodeStore(): LoginCodeStore
    {
        if ($this->loginCodeStore === null) {
            $this->loginCodeStore = new LoginCodeStore();
        }

        return $this->loginCodeStore;
    }

   /**
     * @OA\Get(
     *     path="/v1/tools/user-linked",
     *     summary="用户关联",
     *     description="生成用户关联密钥",
     *     tags={"Tools"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="关联成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="user-linked"),
     *             @OA\Property(property="key", type="string", description="关联密钥")
     *         )
     *     ),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
   public function actionUserLinked(){

    //把 Yii::$app->user->identity 转换成 User 类型

        $user = $this->currentUser();
        $issued = $this->loginCodeStore()->issue((int)$user->id);

        return [
            'success' => true,
            'message' => "user-linked",
            'key'=> $issued['key'],
            'expires_at' => $issued['expires_at'],
            'expires_in' => $issued['expires_in'],
        ];
       
    }

    /**
     * @OA\Get(
     *     path="/v1/tools/user-linked/status",
     *     summary="用户关联密钥状态",
     *     description="检查当前二维码登录码是否仍然有效，不会生成新的登录码",
     *     tags={"Tools"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         required=true,
     *         description="当前二维码中的登录码，可带 web_ 前缀",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="检查成功"),
     *     @OA\Response(response=400, description="请求错误"),
     *     @OA\Response(response=401, description="未授权")
     * )
     */
    public function actionUserLinkedStatus()
    {
        $user = $this->currentUser();
        $key = (string)Yii::$app->request->get('key', '');
        if (LoginCodeStore::normalizeInput($key) === '') {
            throw new BadRequestHttpException("key is required");
        }

        $status = $this->loginCodeStore()->status((int)$user->id, $key);

        $response = [
            'success' => true,
            'message' => 'user-linked-status',
            'active' => $status['active'],
            'reason' => $status['reason'],
        ];

        if (isset($status['expires_at'])) {
            $response['expires_at'] = $status['expires_at'];
            $response['expires_in'] = $status['expires_in'];
        }

        return $response;
    }

    private function currentUser(): User
    {
        $identity = Yii::$app->user->identity;
        if ($identity instanceof User) {
            return $identity;
        }

        throw new \yii\web\UnauthorizedHttpException('Invalid user identity');
    }
}

<?php

namespace api\modules\v1\controllers;
use api\modules\v1\models\UserCreation;
use api\modules\v1\filters\LoginCodeReadinessBehavior;
use mdm\admin\components\AccessControl;
use mdm\admin\components\Helper as AdminHelper;
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
use yii\web\ForbiddenHttpException;
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
            // Status is a companion operation to user-linked and deliberately
            // inherits that existing RBAC route instead of requiring every
            // develop/production role to receive a new route assignment.
            'allowActions' => ['user-linked-status'],
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
        $issued = $this->loginCodeStore()->issue((int)$user->id, $this->loginCodeContext());

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
        if (!$this->canAccessUserLinked()) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }

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

    protected function canAccessUserLinked(): bool
    {
        return AdminHelper::checkRoute(
            '/v1/tools/user-linked',
            Yii::$app->request->get(),
            Yii::$app->user,
        );
    }

    private function currentUser(): User
    {
        $identity = Yii::$app->user->identity;
        if ($identity instanceof User) {
            return $identity;
        }

        throw new \yii\web\UnauthorizedHttpException('Invalid user identity');
    }

    /**
     * Persist only the host from a configured, exact browser origin. The
     * value is white-label routing metadata and is never an authorization
     * input. Requests without a trusted Origin keep the legacy empty context.
     *
     * @return array{frontend_domain?: string}
     */
    private function loginCodeContext(): array
    {
        $origin = $this->normalizeFrontendOrigin(
            (string)Yii::$app->request->getHeaders()->get('Origin', '')
        );
        if ($origin === null) {
            return [];
        }

        $configured = getenv('CORS_ALLOWED_ORIGINS');
        if ($configured === false || trim($configured) === '') {
            return [];
        }

        $allowed = false;
        foreach (explode(',', $configured) as $candidate) {
            if ($this->normalizeFrontendOrigin(trim($candidate)) === $origin) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            return [];
        }

        $host = parse_url($origin, PHP_URL_HOST);
        return is_string($host) && $this->isValidFrontendDomain($host)
            ? ['frontend_domain' => strtolower($host)]
            : [];
    }

    private function normalizeFrontendOrigin(string $candidate): ?string
    {
        if (preg_match(
            '/\A(?<scheme>https?):\/\/(?<host>\[[0-9a-f:.]+\]|[a-z0-9.-]+)(?::(?<port>[0-9]{1,5}))?\z/iD',
            $candidate,
            $parts
        ) !== 1) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        $host = strtolower(trim($parts['host'], '[]'));
        if ($host === '' || str_ends_with($host, '.')) {
            return null;
        }
        if ($scheme === 'http' && !in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return null;
        }

        $port = isset($parts['port']) && $parts['port'] !== '' ? (int)$parts['port'] : null;
        if ($port !== null && ($port < 1 || $port > 65535)) {
            return null;
        }
        if (($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80)) {
            $port = null;
        }

        $serializedHost = str_contains($host, ':') ? '[' . $host . ']' : $host;
        return $scheme . '://' . $serializedHost . ($port === null ? '' : ':' . $port);
    }

    private function isValidFrontendDomain(string $domain): bool
    {
        $domain = strtolower($domain);
        if ($domain === '' || strlen($domain) > 253 || str_ends_with($domain, '.')) {
            return false;
        }
        if ($domain === 'localhost' || filter_var($domain, FILTER_VALIDATE_IP) !== false) {
            return true;
        }

        return preg_match(
            '/\A(?=.{1,253}\z)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\z/D',
            $domain
        ) === 1;
    }
}

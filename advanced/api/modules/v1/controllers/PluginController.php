<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\User;
use common\models\PluginPermissionConfig;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use api\modules\v1\components\RateLimiter;
use api\modules\v1\services\EmailService;
use Yii;
use yii\web\Response;

/**
 * PluginController 提供面向插件体系的认证授权 API
 *
 * 端点：
 * - GET /v1/plugin/verify-token   验证 JWT Token 并返回用户信息
 * - GET /v1/plugin/check-permission  检查用户是否有权限执行某插件操作
 */
class PluginController extends \yii\rest\Controller
{
    /**
     * {@inheritdoc}
     * 移除默认 authenticator，由 action 内手动解析 Token
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     * 限制允许的 HTTP 方法
     */
    protected function verbs()
    {
        return [
            'verify-token' => ['GET'],
            'check-permission' => ['GET'],
            'allowed-actions' => ['GET'],
            'send-email' => ['POST'],
        ];
    }

    /**
     * 从请求中解析 JWT Token 并返回用户信息
     *
     * @param \yii\web\Request $request
     * @return array ['user' => User, 'roles' => string[]]
     */
    protected function resolveUser($request): array
    {
        // 1. 从 Authorization 头提取 Bearer token
        $authHeader = $request->getHeaders()->get('Authorization');
        if (!$authHeader || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            Yii::$app->response->statusCode = 401;
            return ['error' => ['code' => 1001, 'message' => '缺少有效的 Authorization 头']];
        }
        $token = $matches[1];

        // 2. 解析 Token
        try {
            $parsedToken = Yii::$app->jwt->parse($token);
        } catch (InvalidTokenStructure $e) {
            Yii::$app->response->statusCode = 401;
            return ['error' => ['code' => 1003, 'message' => 'Token 无效']];
        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 401;
            return ['error' => ['code' => 1003, 'message' => 'Token 无效']];
        }

        // 3. 验证 Token（签名 + 过期时间）
        try {
            Yii::$app->jwt->assert($parsedToken);
        } catch (RequiredConstraintsViolated $e) {
            // 检查是否是过期错误
            $message = $e->getMessage();
            if (stripos($message, 'expired') !== false || stripos($message, 'not yet valid') !== false) {
                Yii::$app->response->statusCode = 401;
                return ['error' => ['code' => 1002, 'message' => 'Token 已过期']];
            }
            Yii::$app->response->statusCode = 401;
            return ['error' => ['code' => 1003, 'message' => 'Token 无效']];
        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 401;
            return ['error' => ['code' => 1003, 'message' => 'Token 无效']];
        }

        // 4. 从 claims 获取 uid，查找用户
        $claims = $parsedToken->claims();
        $uid = $claims->get('uid');
        $user = User::findIdentity($uid);
        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return ['error' => ['code' => 1004, 'message' => '用户不存在']];
        }

        // 5. 获取用户角色
        $roles = array_keys(Yii::$app->authManager->getRolesByUser($uid));

        return ['user' => $user, 'roles' => $roles];
    }

    /**
     * GET /v1/plugin/verify-token
     * 验证 JWT Token 并返回用户信息
     */
    public function actionVerifyToken()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = $this->resolveUser(Yii::$app->request);

        if (isset($result['error'])) {
            return $result['error'];
        }

        /** @var User $user */
        $user = $result['user'];
        $roles = $result['roles'];

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'roles' => $roles,
            ],
        ];
    }

    /**
     * GET /v1/plugin/check-permission
     * 检查用户是否有权限执行某插件操作
     */
    public function actionCheckPermission()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $pluginName = $request->get('plugin_name');
        $action = $request->get('action');

        // 验证必要参数
        if (empty($pluginName) || empty($action)) {
            Yii::$app->response->statusCode = 400;
            return [
                'code' => 2001,
                'message' => '缺少必要参数: plugin_name, action',
            ];
        }

        $result = $this->resolveUser($request);

        if (isset($result['error'])) {
            return $result['error'];
        }

        /** @var User $user */
        $user = $result['user'];
        $roles = $result['roles'];

        // 查询 plugin_permission_config 表判定权限
        $allowed = PluginPermissionConfig::checkPermission($roles, $pluginName, $action);

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'allowed' => $allowed,
                'user_id' => $user->id,
                'roles' => $roles,
            ],
        ];
    }

    /**
     * GET /v1/plugin/allowed-actions
     * 批量获取用户在某插件下的所有允许操作
     */
    public function actionAllowedActions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $pluginName = $request->get('plugin_name');

        if (empty($pluginName)) {
            Yii::$app->response->statusCode = 400;
            return [
                'code' => 2001,
                'message' => '缺少必要参数: plugin_name',
            ];
        }

        $result = $this->resolveUser($request);

        if (isset($result['error'])) {
            return $result['error'];
        }

        /** @var User $user */
        $user = $result['user'];
        $roles = $result['roles'];

        $actions = PluginPermissionConfig::getAllowedActions($roles, $pluginName);

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'actions' => $actions,
                'user_id' => $user->id,
                'roles' => $roles,
            ],
        ];
    }

    /**
     * POST /v1/plugin/send-email
     * 插件通过主后端发送邮件（如邮箱验证码）
     *
     * 请求体：
     * - email: string 收件人邮箱
     * - type: string 邮件类型（仅支持 verification_code）
     * - params: array 模板参数（可选，locale / i18n）
     *
     * 速率限制：同一邮箱 60 秒 1 次，同一用户 60 秒 1 次
     */
    public function actionSendEmail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;

        // 1. 验证 JWT token
        $result = $this->resolveUser($request);
        if (isset($result['error'])) {
            return $result['error'];
        }

        /** @var User $user */
        $user = $result['user'];

        // 2. 解析请求体
        $email = $request->getBodyParam('email');
        $type = $request->getBodyParam('type');
        $params = $request->getBodyParam('params', []);

        if (empty($email) || empty($type)) {
            Yii::$app->response->statusCode = 400;
            return [
                'code' => 3001,
                'message' => '缺少必要参数: email, type',
            ];
        }

        // 3. 仅支持 verification_code 类型
        $allowedTypes = ['verification_code'];
        if (!in_array($type, $allowedTypes, true)) {
            Yii::$app->response->statusCode = 400;
            return [
                'code' => 3002,
                'message' => '不支持的邮件类型: ' . $type . '，仅支持: ' . implode(', ', $allowedTypes),
            ];
        }

        // 4. 验证邮箱格式
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Yii::$app->response->statusCode = 400;
            return [
                'code' => 3003,
                'message' => '邮箱格式无效',
            ];
        }

        // 5. Redis 速率限制
        $rateLimiter = new RateLimiter();
        $emailRateKey = 'plugin:send_email:email:' . strtolower(trim($email));
        $userRateKey = 'plugin:send_email:user:' . $user->id;
        $rateLimitWindow = 60; // 60 秒

        // 5a. 同一邮箱 60 秒内 1 次
        if ($rateLimiter->tooManyAttempts($emailRateKey, 1, $rateLimitWindow)) {
            $retryAfter = $rateLimiter->availableIn($emailRateKey);
            Yii::$app->response->statusCode = 429;
            return [
                'code' => 3004,
                'message' => "发送过于频繁，请 {$retryAfter} 秒后再试",
                'data' => ['retry_after' => $retryAfter],
            ];
        }

        // 5b. 同一用户 60 秒内 1 次
        if ($rateLimiter->tooManyAttempts($userRateKey, 1, $rateLimitWindow)) {
            $retryAfter = $rateLimiter->availableIn($userRateKey);
            Yii::$app->response->statusCode = 429;
            return [
                'code' => 3004,
                'message' => "发送过于频繁，请 {$retryAfter} 秒后再试",
                'data' => ['retry_after' => $retryAfter],
            ];
        }

        // 6. 生成验证码并存储到 Redis
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $redis = Yii::$app->redis;
        $codeKey = 'plugin:verify_code:' . strtolower(trim($email));
        $codeData = json_encode([
            'code' => $code,
            'created_at' => time(),
        ]);
        $redis->executeCommand('SETEX', [$codeKey, 900, $codeData]); // 15 分钟过期

        // 7. 复用 EmailService 发送邮件
        $emailService = new EmailService();
        $locale = $params['locale'] ?? 'en-US';
        $i18n = $params['i18n'] ?? [];
        $sent = $emailService->sendVerificationCode($email, $code, $locale, $i18n);

        // 8. 记录速率限制（无论发送是否成功都记录，防止滥用）
        $rateLimiter->hit($emailRateKey, $rateLimitWindow);
        $rateLimiter->hit($userRateKey, $rateLimitWindow);

        if (!$sent) {
            Yii::warning("Plugin send-email: failed to send to {$email}", __METHOD__);
            Yii::$app->response->statusCode = 500;
            return [
                'code' => 3005,
                'message' => '邮件发送失败，请稍后重试',
            ];
        }

        Yii::info("Plugin send-email: verification code sent to {$email} by user {$user->id}", __METHOD__);

        return [
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'email' => $email,
                'type' => $type,
            ],
        ];
    }
}

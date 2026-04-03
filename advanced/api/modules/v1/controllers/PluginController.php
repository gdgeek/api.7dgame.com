<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\User;
use common\models\PluginPermissionConfig;
use api\modules\v1\components\RateLimiter;
use api\modules\v1\services\EmailService;
use mdm\admin\components\AccessControl;
use bizley\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
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
     * 使用标准 JWT 认证 + RBAC 权限控制
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                JwtHttpBearerAuth::class,
            ],
            'except' => ['options', 'list'],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'allowActions' => ['options', 'list'],
        ];

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
            'list' => ['GET'],
        ];
    }

    /**
     * 获取当前已认证用户及其角色
     *
     * @return array ['user' => User, 'roles' => string[]]
     */
    protected function resolveUser(): array
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $roles = array_keys(Yii::$app->authManager->getRolesByUser($user->id));

        return ['user' => $user, 'roles' => $roles];
    }

    /**
     * GET /v1/plugin/verify-token
     * 验证 JWT Token 并返回用户信息
     */
    public function actionVerifyToken()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $result = $this->resolveUser();

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

        $result = $this->resolveUser();

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

        $result = $this->resolveUser();

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

        // 1. 获取已认证用户
        $result = $this->resolveUser();
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

    /**
     * 按 id 字段合并两个行数组，$override 中同 id 的记录覆盖 $base，新 id 追加。
     */
    private function mergeById(array $base, array $override): array
    {
        if (empty($override)) return $base;
        $map = [];
        foreach ($base as $row) {
            $map[$row['id']] = $row;
        }
        foreach ($override as $row) {
            $map[$row['id']] = $row;
        }
        return array_values($map);
    }

    /**
     * GET /v1/plugin/list
     * 获取插件列表和菜单分组（三层合并：通用默认 + 域名专属叠加）
     */
    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $domain = Yii::$app->request->get('domain');

        try {
            $pluginDb = Yii::$app->get('pluginDb');
        } catch (\Throwable $e) {
            Yii::error('[PluginController] Failed to get pluginDb: ' . $e->getMessage(), 'plugin');
            return [
                'code' => 5001,
                'message' => 'pluginDb connection failed: ' . $e->getMessage(),
            ];
        }

        try {
            // 查询菜单分组：通用（domain=NULL）为基础，域名专属按 id 叠加覆盖
            $generalGroups = (new \yii\db\Query())->from('plugin_menu_groups')
                ->where(['domain' => null])->orderBy(['order' => SORT_ASC])->all($pluginDb);
            $domainGroups = $domain ? (new \yii\db\Query())->from('plugin_menu_groups')
                ->where(['domain' => $domain])->orderBy(['order' => SORT_ASC])->all($pluginDb) : [];
            $groups = $this->mergeById($generalGroups, $domainGroups);

            // 查询插件列表：通用（domain=NULL）为基础，域名专属按 id 叠加覆盖
            $generalPlugins = (new \yii\db\Query())->from('plugins')
                ->where(['domain' => null, 'enabled' => 1])->orderBy(['order' => SORT_ASC])->all($pluginDb);
            $domainPlugins = $domain ? (new \yii\db\Query())->from('plugins')
                ->where(['domain' => $domain, 'enabled' => 1])->orderBy(['order' => SORT_ASC])->all($pluginDb) : [];
            $plugins = $this->mergeById($generalPlugins, $domainPlugins);
        } catch (\Throwable $e) {
            Yii::error('[PluginController] actionList query failed (pluginDb): ' . $e->getMessage(), 'plugin');
            return [
                'code' => 5000,
                'message' => 'pluginDb query failed: ' . $e->getMessage(),
            ];
        }

        // 转换字段名以兼容前端 plugins.json 格式
        $formattedGroups = array_map(function ($g) {
            return [
                'id' => $g['id'],
                'name' => $g['name'],
                'nameI18n' => $this->decodeJsonField($g['name_i18n']),
                'icon' => $g['icon'],
                'order' => (int)$g['order'],
            ];
        }, $groups);

        $formattedPlugins = array_map(function ($p) {
            return [
                'id' => $p['id'],
                'name' => $p['name'],
                'nameI18n' => $this->decodeJsonField($p['name_i18n']),
                'description' => $p['description'],
                'url' => $p['url'],
                'icon' => $p['icon'],
                'group' => $p['group_id'],
                'enabled' => (bool)$p['enabled'],
                'order' => (int)$p['order'],
                'allowedOrigin' => $p['allowed_origin'],
                'version' => $p['version'],
            ];
        }, $plugins);

        return [
            'version' => '1.0.0',
            'menuGroups' => $formattedGroups,
            'plugins' => $formattedPlugins,
        ];
    }

    /**
     * 安全解码 JSON 字段，处理可能的双重编码（MySQL JSON 列有时返回字符串而非数组）
     */
    private function decodeJsonField($value)
    {
        if ($value === null || $value === '') return null;
        if (is_array($value)) return $value;
        $decoded = json_decode($value, true);
        // 如果解码后仍是字符串，说明被双重编码了，再解一次
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }
        return is_array($decoded) ? $decoded : null;
    }
}

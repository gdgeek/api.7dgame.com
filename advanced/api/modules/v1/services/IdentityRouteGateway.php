<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;

/** Backward-compatible user routes terminate at the sole identity writer. */
class IdentityRouteGateway extends Component
{
    public ?IdentityProviderClient $client = null;

    public function forward(string $route): ?array
    {
        if (!IndependentIdentity::enabled() || Yii::$app->request->isOptions) {
            return null;
        }
        $request = Yii::$app->request;
        $query = $request->getQueryParams();
        $target = $this->target($route, $query);
        if ($target === null) {
            return null;
        }

        $method = $request->method;
        $payload = $request->getBodyParams();
        $context = [
            'authorization' => $request->headers->get('Authorization'), 'ip' => $request->userIP,
            'user_agent' => $request->userAgent, 'idempotency_key' => $request->headers->get('Idempotency-Key'),
        ];
        if (in_array($route, ['wechat/qrcode', 'site/wechat-qrcode', 'wechat/openid', 'site/wechat-openid',
            'wechat/signup', 'wechat/binding'], true)) {
            return (new LegacyWechatGateway($this->client ??= new IdentityProviderClient()))->forward($route, $payload, $query, $context);
        }
        if (in_array($route, ['v1/site/apple-id', 'v1/site/apple-id-create', 'v1/site/apple-id-link'], true)) {
            return (new AppleIdentityProvider(['client' => $this->client]))->forward(substr($route, strlen('v1/site/')),
                $payload, ['ip' => $request->userIP, 'user_agent' => $request->userAgent,
                    'idempotency_key' => $request->headers->get('Idempotency-Key')]);
        }
        if ($route === 'server/reset-password') {
            $payload = [
                'old_password' => $payload['oldPassword'] ?? null,
                'new_password' => $payload['password'] ?? null,
                'confirm_password' => $payload['password'] ?? null,
            ];
            $method = 'POST';
        }
        if ($route === 'v1/user/update' && isset($payload['avatar_id'])) {
            $avatarId = filter_var($payload['avatar_id'], FILTER_VALIDATE_INT);
            if ($avatarId === false || $avatarId <= 0 || \api\modules\v1\models\File::findOne($avatarId) === null) {
                throw new \yii\web\BadRequestHttpException('Avatar file does not exist.');
            }
        }
        // The Identity route itself authenticates and authorizes the caller.
        // A browser cannot select a legacy path with migration marker headers.
        $result = ($this->client ??= new IdentityProviderClient())->proxyAccountLifecycle(
            $method,
            $target,
            in_array($method, ['GET', 'HEAD'], true) ? null : $payload,
            $query,
            [
                'authorization' => $request->headers->get('Authorization'),
                'ip' => $request->userIP,
                'user_agent' => $request->userAgent,
                'idempotency_key' => $request->headers->get('Idempotency-Key'),
            ]
        );
        if (in_array($route, ['site/login', 'v1/site/login'], true) && $result['status'] >= 200 && $result['status'] < 300) {
            $tokens = $result['body']['token'] ?? null;
            $access = is_array($tokens) ? ($tokens['accessToken'] ?? $tokens['token'] ?? null) : null;
            if (!is_string($access) || $access === '') {
                throw new \common\components\security\ServiceUnavailableHttpException('Identity returned no login access token.');
            }
            $subject = $this->client->verifyIdentityAccessToken($access);
            $result['body'] = ['nickname' => $subject['nickname'] ?? null,
                'username' => $subject['username'], 'roles' => $subject['roles'], 'auth' => $access];
            if ($route === 'site/login') {
                $result['body']['token'] = $access;
            }
        }
        if ($route === 'v1/user/update' && $result['status'] >= 200 && $result['status'] < 300
            && ($result['body']['success'] ?? false) === true) {
            $authorization = (string)$request->headers->get('Authorization', '');
            if (preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches) !== 1) {
                throw new \yii\web\UnauthorizedHttpException('Invalid user identity.');
            }
            $user = IndependentIdentity::authority()->verifyAccessToken($matches[1]);
            $result['body'] = [
                'success' => true, 'message' => 'ok',
                'data' => (new UserManagementService())->buildCurrentUserPayload($user),
            ];
        }
        if (str_starts_with($route, 'v1/person/') && $result['status'] >= 200 && $result['status'] < 300) {
            $result['body'] = $this->aggregatePersonAvatars($result['body']);
        }
        return $result;
    }

    public function target(string $route, array $query = []): ?string
    {
        // Existing forms retain their pages; shared models call Identity.
        if (in_array($route, ['site/signup', 'site/request-password-reset', 'site/reset-password', 'site/verify-email',
            'site/resend-verification-email', 'site/password-reset-token', 'entry/login', 'entry/signup',
            'entry/request-password-reset', 'entry/reset-password', 'entry/verify-email', 'entry/resend-verification-email',
            'home/invitation', 'home/add-invitation', 'info/create-invitation'], true)
            || ($route === 'site/login' && Yii::$app->controllerNamespace === 'backend\\controllers')) {
            return null;
        }
        if (str_starts_with($route, 'admin/')) {
            // Original MDM controllers use the remote authManager adapter;
            // User/Assignment controllers are mapped to explicit adapters.
            return null;
        }
        // user/info combines Identity's profile with MRPP-owned points/avatar.
        if (in_array($route, ['v1/user/info', 'v1/user/creation', 'v1/email/test'], true)) {
            return null;
        }
        // Yii dispatches REST URLs as action IDs; Identity exposes the REST
        // paths themselves. Route params are already part of Request query.
        if (in_array($route, ['v1/person/index', 'v1/person/create'], true)) {
            return '/v1/person';
        }
        if (in_array($route, ['v1/person/view', 'v1/person/update', 'v1/person/delete'], true)) {
            $id = filter_var($query['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new \yii\web\BadRequestHttpException('Invalid person ID.');
            }
            return '/v1/person/' . $id;
        }
        foreach (['v1/auth/', 'v1/plugin-user/', 'v1/organization/', 'v1/password/', 'v1/email/', 'v1/wechat/', 'v1/user/', 'v1/person/'] as $prefix) {
            if (str_starts_with($route, $prefix)) {
                return '/' . $route;
            }
        }
        $aliases = [
            'wechat/qrcode' => '/v1/wechat/qrcode',
            'site/wechat-qrcode' => '/v1/wechat/qrcode',
            'wechat/openid' => '/v1/wechat/refresh',
            'site/wechat-openid' => '/v1/wechat/refresh',
            'wechat/signup' => '/v1/wechat/register',
            'wechat/binding' => '/v1/wechat/binding',
            'v1/plugin-campus/password' => '/v1/plugin-campus/users/password',
            'v1/tools/user-linked' => '/v1/tools/user-linked',
            'v1/tools/user-linked-status' => '/v1/tools/user-linked/status',
            'v1/site/login' => '/v1/auth/login',
            'site/login' => '/v1/auth/login',
            'entry/login' => '/v1/auth/login',
            'server/reset-password' => '/v1/password/change',
        ];
        if (isset($aliases[$route])) {
            return $aliases[$route];
        }
        // Legacy account controllers must not remain a second registration,
        // third-party binding, or password authority. An unsupported target
        // returns Identity's error instead of executing the old controller.
        foreach (['v1/site/apple-', 'wechat/binding', 'wechat/signup', 'wechat/openid', 'wechat/qrcode', 'wechat/index',
            'site/wechat-', 'site/signup', 'site/request-password-reset', 'site/reset-password', 'site/verify-email',
            'site/resend-verification-email', 'site/password-reset-token', 'entry/signup', 'entry/request-password-reset',
            'entry/reset-password', 'entry/verify-email', 'entry/resend-verification-email', 'home/invitation',
            'home/add-invitation', 'info/create-invitation', 'admin/assignment/', 'admin/user/', 'admin/role/',
            'admin/permission/', 'admin/route/', 'admin/rule/'] as $prefix) {
            if (str_starts_with($route, $prefix)) {
                return '/v1/' . (str_starts_with($route, 'v1/') ? substr($route, 3) : $route);
            }
        }

        return null;
    }

    private function aggregatePersonAvatars(array $body): array
    {
        $isList = array_is_list($body);
        $people = $isList ? $body : [$body];
        $ids = [];
        foreach ($people as $person) {
            if (is_array($person) && isset($person['avatar_id'])) {
                $id = filter_var($person['avatar_id'], FILTER_VALIDATE_INT);
                if ($id !== false && $id > 0) {
                    $ids[$id] = $id;
                }
            }
        }
        $avatars = $ids === [] ? [] : $this->loadPersonAvatars(array_values($ids));
        foreach ($people as &$person) {
            if (is_array($person) && array_key_exists('avatar_id', $person)) {
                $id = filter_var($person['avatar_id'], FILTER_VALIDATE_INT);
                $person['avatar'] = $id !== false && $id > 0 ? ($avatars[$id] ?? null) : null;
                unset($person['avatar_id']);
            }
        }
        unset($person);
        return $isList ? $people : $people[0];
    }

    protected function loadPersonAvatars(array $ids): array
    {
        $avatars = [];
        foreach (\api\modules\v1\models\File::find()->where(['id' => $ids])->all() as $file) {
            $avatars[(int)$file->id] = $file->toArray();
        }
        return $avatars;
    }
}

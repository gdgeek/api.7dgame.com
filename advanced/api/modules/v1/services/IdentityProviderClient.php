<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use common\components\security\ServiceUnavailableHttpException;

class IdentityProviderClient extends Component
{
    public function legacyAdmin(string $operation, array $payload = [], ?string $accessToken = null): array
    {
        return $this->trustedLegacyOperation('/internal/legacy-admin', $this->legacyAdminEnvelope($operation, $payload, $accessToken), []);
    }

    private function legacyAdminEnvelope(string $operation, array $payload, ?string $accessToken): array
    {
        $envelope = compact('operation', 'payload', 'accessToken');
        // Derive delegated MDM authorization only from the executing Yii route.
        // Browser payload fields can never select the actor or permission.
        $route = isset(Yii::$app->controller) ? Yii::$app->controller->getRoute() : '';
        if (preg_match('~^admin/[a-z0-9-]+/[a-z0-9-]+$~D', $route) === 1) {
            $envelope['permissionName'] = '/' . $route;
        }
        return $envelope;
    }

    public function legacyPhpSession(int $userId, string $authKey, array $context = []): array
    {
        $response = $this->trustedLegacyOperation('/internal/legacy-php-session/exchange', compact('userId', 'authKey'), $context);
        return $this->tokenFromResponse($response);
    }

    public function legacyEmailLink(string $operation, array $payload, array $context = []): array
    {
        if (!in_array($operation, ['validate-reset', 'reset', 'verify', 'request-reset', 'resend-verification', 'signup'], true)) {
            throw new BadRequestHttpException('Invalid legacy email link operation.');
        }
        return $this->trustedLegacyOperation('/internal/legacy-email-link/' . $operation, $payload, $context);
    }

    private function trustedLegacyOperation(string $path, array $payload, array $context): array
    {
        $token = $this->internalAuthToken();
        if ($token === null) {
            throw new ServiceUnavailableHttpException('Identity compatibility internal credential is not configured.');
        }
        return $this->requestJson('POST', $path, $payload, [],
            array_merge($context, ['identity_internal_token' => $token]), false)['body'];
    }

    public function verifiedApple(string $operation, array $payload, array $context = []): array
    {
        if (!in_array($operation, ['resolve', 'create', 'link'], true)) {
            throw new BadRequestHttpException('Invalid Apple identity operation.');
        }
        $token = $this->internalAuthToken();
        if ($token === null) {
            throw new ServiceUnavailableHttpException('Identity Apple internal credential is not configured.');
        }
        return $this->requestJson('POST', '/internal/apple/' . $operation, $payload, [],
            array_merge($context, ['identity_internal_token' => $token]), true);
    }

    public function identitySubject(int $id): array
    {
        return $this->requiredInternalData('GET', '/internal/identity/subjects/' . $id);
    }

    public function identityPolicyGraph(): array
    {
        return $this->requiredInternalData('POST', '/internal/identity/policy-graph');
    }

    public function verifyIdentityAccessToken(string $token): array
    {
        return $this->requiredInternalData('POST', '/internal/auth/verify-access-token', ['token' => $token]);
    }

    public function identityOrganizationMembers(int $id): array
    {
        return $this->requiredInternalData('GET', '/internal/identity/organizations/' . $id . '/members');
    }

    public function identityPermission(int $id, string $permission): bool
    {
        $data = $this->requiredInternalData('POST', '/internal/identity/authorize', [
            'legacyUserId' => $id, 'permission' => $permission,
        ]);
        if (!isset($data['allowed']) || !is_bool($data['allowed'])) {
            throw new ServiceUnavailableHttpException('Identity returned an invalid authorization decision.');
        }
        return $data['allowed'];
    }

    public function identityProjectionEvents(int $afterId, int $limit = 100): array
    {
        return $this->requiredInternalData('GET', '/internal/identity/events?'
            . http_build_query(['afterId' => $afterId, 'limit' => max(1, min(500, $limit))]));
    }

    private function requiredInternalData(string $method, string $path, ?array $payload = null): array
    {
        $token = $this->internalIamToken();
        if ($token === null) {
            throw new ServiceUnavailableHttpException('Identity internal credential is not configured.');
        }
        $response = $this->requestJson($method, $path, $payload, [], ['identity_internal_token' => $token], true);
        $status = (int)($response['status'] ?? 0);
        if (in_array($status, [401, 403, 404], true)) {
            throw new UnauthorizedHttpException('Identity rejected the subject or token.');
        }
        $data = $response['body']['data'] ?? null;
        if ($status < 200 || $status >= 300 || !is_array($data)) {
            throw new ServiceUnavailableHttpException('Identity authority is unavailable.');
        }
        return $data;
    }

    public function login(string $username, string $password, array $context = []): array
    {
        $response = $this->postJson('/v1/auth/login', [
            'username' => $username,
            'password' => $password,
        ], $context);

        return $this->tokenFromResponse($response);
    }

    public function refresh(string $refreshToken, array $context = []): array
    {
        $response = $this->postJson('/v1/auth/refresh', [
            'refreshToken' => $refreshToken,
        ], $context);

        return $this->tokenFromResponse($response);
    }

    public function logout(?string $refreshToken, array $context = []): bool
    {
        $response = $this->postJson('/v1/auth/logout', [
            'refreshToken' => $refreshToken,
        ], $context);

        return (bool)($response['revoked'] ?? true);
    }

    public function issueUserToken(int $legacyUserId, array $context = []): array
    {
        $token = $this->internalAuthToken();
        if ($token === null) {
            throw new ServerErrorHttpException('IDENTITY_INTERNAL_API_TOKEN is required for identity user token issuance.');
        }

        $response = $this->postJson('/internal/auth/issue-user-token', [
            'legacyUserId' => $legacyUserId,
        ], array_merge($context, [
            'identity_internal_token' => $token,
        ]));

        return $this->tokenFromResponse($response);
    }

    public function proxyAccountLifecycle(
        string $method,
        string $path,
        ?array $payload = null,
        array $query = [],
        array $context = []
    ): array {
        return $this->requestJson($method, $path, $payload, $query, $context, true);
    }

    public function iamUserView(int $legacyUserId): ?array
    {
        return $this->internalIamData('GET', '/internal/iam/users/' . $legacyUserId);
    }

    public function iamRolesView(int $legacyUserId): ?array
    {
        return $this->internalIamData('GET', '/internal/iam/users/' . $legacyUserId . '/roles');
    }

    public function iamPermissionsView(int $legacyUserId): ?array
    {
        return $this->internalIamData('GET', '/internal/iam/users/' . $legacyUserId . '/permissions');
    }

    public function iamOrganizationsView(int $legacyUserId): ?array
    {
        return $this->internalIamData('GET', '/internal/iam/users/' . $legacyUserId . '/organizations');
    }

    public function iamPluginVerifyToken(string $token): ?array
    {
        return $this->internalIamData('POST', '/internal/iam/plugin/verify-token', ['token' => $token]);
    }

    public function iamAuthzResolve(array $input): ?array
    {
        return $this->internalIamData('POST', '/internal/iam/authz/resolve', $input);
    }

    private function tokenFromResponse(array $response): array
    {
        if (!isset($response['token']) || !is_array($response['token'])) {
            throw new ServerErrorHttpException('Identity provider response is missing token.');
        }

        return $response['token'];
    }

    private function postJson(string $path, array $payload, array $context): array
    {
        $response = $this->requestJson('POST', $path, $payload, [], $context, false);

        return $response['body'];
    }

    private function requestJson(
        string $method,
        string $path,
        ?array $payload,
        array $query,
        array $context,
        bool $preserveHttpErrors
    ): array
    {
        if (!function_exists('curl_init')) {
            throw new ServerErrorHttpException('curl extension is required for identity provider proxy.');
        }

        $baseUrl = $this->baseUrl();
        if ($baseUrl === null) {
            throw new ServerErrorHttpException('IDENTITY_AUTH_BASE_URL is required when AUTH_PROVIDER=identity.');
        }

        $body = null;
        if (!in_array(strtoupper($method), ['GET', 'HEAD'], true)) {
            $body = json_encode($payload ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($body === false) {
                throw new BadRequestHttpException('Identity provider payload encoding failed.');
            }
        }

        $headers = [
            'Accept: application/json',
        ];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }
        if (!empty($context['ip'])) {
            $headers[] = 'X-Forwarded-For: ' . $context['ip'];
        }
        if (!empty($context['user_agent'])) {
            $headers[] = 'User-Agent: ' . $context['user_agent'];
        }
        if (!empty($context['authorization'])) {
            $headers[] = 'Authorization: ' . $context['authorization'];
        }
        if (!empty($context['identity_internal_token'])) {
            $headers[] = 'X-Identity-Internal-Token: ' . $context['identity_internal_token'];
        }
        if (!empty($context['idempotency_key'])) {
            $key = (string)$context['idempotency_key'];
            if (strlen($key) <= 128 && preg_match('/^[A-Za-z0-9._:-]+$/', $key) === 1) {
                $headers[] = 'Idempotency-Key: ' . $key;
            }
        }

        $url = rtrim($baseUrl, '/') . $path;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $handle = curl_init($url);
        $responseHeaders = [];
        curl_setopt($handle, CURLOPT_HEADERFUNCTION, static function ($handle, string $line) use (&$responseHeaders): int {
            if (preg_match('/^(X-Pagination-(?:Total-Count|Page-Count|Current-Page|Per-Page)):\s*(\d+)\s*$/i', trim($line), $matches) === 1) {
                $responseHeaders[$matches[1]] = $matches[2];
            }
            return strlen($line);
        });
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeoutMs());
        curl_setopt($handle, CURLOPT_TIMEOUT_MS, $this->timeoutMs());
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($raw === false || $error !== '') {
            Yii::warning('Identity provider transport failed: ' . $error, 'identity.auth');
            throw new ServiceUnavailableHttpException('Identity provider is unavailable.');
        }

        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded)) {
            throw new ServerErrorHttpException('Identity provider response is not valid JSON.');
        }

        if ($preserveHttpErrors) {
            return ['status' => $status, 'body' => $decoded, 'headers' => $responseHeaders];
        }

        if ($status === 401) {
            throw new UnauthorizedHttpException($decoded['message'] ?? 'Identity provider rejected the request.');
        }
        if ($status >= 400 && $status < 500) {
            throw new BadRequestHttpException($decoded['message'] ?? 'Identity provider request failed.');
        }
        if ($status >= 500) {
            throw new ServerErrorHttpException('Identity provider server error.');
        }

        return ['status' => $status, 'body' => $decoded];
    }

    private function baseUrl(): ?string
    {
        return $this->stringConfig('IDENTITY_AUTH_BASE_URL');
    }

    private function timeoutMs(): int
    {
        return $this->intConfig('IDENTITY_AUTH_TIMEOUT_MS', 1500, 100, 10000);
    }

    private function connectTimeoutMs(): int
    {
        return $this->intConfig('IDENTITY_AUTH_CONNECT_TIMEOUT_MS', 300, 50, 5000);
    }

    private function internalIamData(string $method, string $path, ?array $payload = null): ?array
    {
        $token = $this->internalIamToken();
        if ($token === null) {
            Yii::warning('Identity IAM shadow compare skipped because internal token is not configured.', 'identity.iamShadowCompare');
            return null;
        }

        $response = $this->requestJson($method, $path, $payload, [], [
            'identity_internal_token' => $token,
        ], true);

        $status = (int)($response['status'] ?? 0);
        if ($status >= 400) {
            Yii::warning('Identity IAM shadow compare endpoint returned HTTP ' . $status . '.', 'identity.iamShadowCompare');
            return null;
        }

        $body = $response['body'] ?? [];
        return is_array($body) && isset($body['data']) && is_array($body['data']) ? $body['data'] : null;
    }

    private function internalAuthToken(): ?string
    {
        return $this->stringConfig('IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN')
            ?? $this->stringConfig('IDENTITY_ACCOUNT_INTERNAL_TOKEN')
            ?? $this->stringConfig('IDENTITY_INTERNAL_API_TOKEN');
    }

    private function internalIamToken(): ?string
    {
        return $this->stringConfig('IDENTITY_IAM_INTERNAL_API_TOKEN')
            ?? $this->stringConfig('IDENTITY_INTERNAL_API_TOKEN');
    }

    private function stringConfig(string $key): ?string
    {
        $value = getenv($key);
        if ($value === false && isset(Yii::$app->params['identityAuth'][$key])) {
            $value = Yii::$app->params['identityAuth'][$key];
        }
        if ($value === false || $value === null) {
            return null;
        }

        $trimmed = trim((string)$value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function intConfig(string $key, int $default, int $min, int $max): int
    {
        $value = getenv($key);
        if ($value === false && isset(Yii::$app->params['identityAuth'][$key])) {
            $value = Yii::$app->params['identityAuth'][$key];
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT);
        if ($parsed === false) {
            return $default;
        }

        return max($min, min($max, (int)$parsed));
    }
}

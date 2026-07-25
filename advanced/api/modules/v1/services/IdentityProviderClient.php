<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class IdentityProviderClient extends Component
{
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

        $url = rtrim($baseUrl, '/') . $path;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $handle = curl_init($url);
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
            throw new ServerErrorHttpException('Identity provider is unavailable.');
        }

        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded)) {
            throw new ServerErrorHttpException('Identity provider response is not valid JSON.');
        }

        if ($preserveHttpErrors) {
            return ['status' => $status, 'body' => $decoded];
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

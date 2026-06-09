<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\Component;
use yii\web\Request;

class AccountLifecycleProxyService extends Component
{
    private ?IdentityProviderClient $identityProviderClient = null;

    public function proxyCurrentRequest(string $scope, string $path): ?array
    {
        if (!$this->shouldProxy($scope)) {
            return null;
        }

        $request = Yii::$app->request;
        try {
            $result = $this->identityProviderClient()->proxyAccountLifecycle(
                $request->method,
                $path,
                $this->requestPayload($request),
                $request->getQueryParams(),
                $this->requestContext($request)
            );

            if ($this->shouldFallbackFromProxyResult($result)) {
                Yii::warning("Identity account lifecycle proxy disabled or unavailable; falling back to legacy {$scope}.", 'identity.accountLifecycle');
                return null;
            }

            Yii::$app->response->statusCode = (int)($result['status'] ?? 200);

            return ['body' => $result['body'] ?? []];
        } catch (\Throwable $exception) {
            Yii::warning("Identity account lifecycle proxy failed for {$scope}: " . $exception->getMessage(), 'identity.accountLifecycle');
            if ($this->fallbackEnabled()) {
                return null;
            }

            throw $exception;
        }
    }

    public function shouldProxy(string $scope): bool
    {
        if ($this->hasLifecycleProxyMarker()) {
            return false;
        }
        if ($this->provider() !== 'identity') {
            return false;
        }
        if (!$this->boolConfig('IDENTITY_ACCOUNT_LIFECYCLE_ENABLED', false)) {
            return false;
        }

        return $this->scopeEnabled($scope);
    }

    public function identityProviderClient(): IdentityProviderClient
    {
        if ($this->identityProviderClient === null) {
            $this->identityProviderClient = new IdentityProviderClient();
        }

        return $this->identityProviderClient;
    }

    private function requestPayload(Request $request): ?array
    {
        if (in_array(strtoupper($request->method), ['GET', 'HEAD'], true)) {
            return null;
        }

        return $request->getBodyParams();
    }

    private function requestContext(Request $request): array
    {
        return [
            'ip' => $request->userIP,
            'user_agent' => $request->userAgent,
            'authorization' => $request->headers->get('Authorization'),
        ];
    }

    private function shouldFallbackFromProxyResult(array $result): bool
    {
        if (!$this->fallbackEnabled()) {
            return false;
        }

        $status = (int)($result['status'] ?? 0);
        $body = $result['body'] ?? [];
        $code = is_array($body) ? (string)($body['code'] ?? '') : '';

        return $status >= 500 || in_array($code, [
            'ACCOUNT_LIFECYCLE_DISABLED',
            'ACCOUNT_LIFECYCLE_SCOPE_DISABLED',
            'ACCOUNT_LIFECYCLE_MODE_DISABLED',
            'ACCOUNT_LIFECYCLE_LEGACY_API_NOT_CONFIGURED',
        ], true);
    }

    private function provider(): string
    {
        $provider = strtolower((string)($this->stringConfig('IDENTITY_ACCOUNT_LIFECYCLE_PROVIDER') ?? 'legacy'));

        return $provider === 'identity' ? 'identity' : 'legacy';
    }

    private function scopeEnabled(string $scope): bool
    {
        $map = [
            'register' => 'IDENTITY_ACCOUNT_REGISTER_ENABLED',
            'password' => 'IDENTITY_ACCOUNT_PASSWORD_ENABLED',
            'email' => 'IDENTITY_ACCOUNT_EMAIL_ENABLED',
            'invitation' => 'IDENTITY_ACCOUNT_INVITATION_ENABLED',
        ];

        return isset($map[$scope]) && $this->boolConfig($map[$scope], false);
    }

    private function hasLifecycleProxyMarker(): bool
    {
        return Yii::$app->request->headers->get('X-Identity-Lifecycle-Proxy') !== null;
    }

    private function fallbackEnabled(): bool
    {
        return $this->boolConfig('IDENTITY_ACCOUNT_LIFECYCLE_FALLBACK', true);
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

    private function boolConfig(string $key, bool $default): bool
    {
        $value = getenv($key);
        if ($value === false && isset(Yii::$app->params['identityAuth'][$key])) {
            $value = Yii::$app->params['identityAuth'][$key];
        }
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string)$value), ['1', 'true', 'yes', 'on'], true);
    }
}

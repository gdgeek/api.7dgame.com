<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use Yii;
use yii\base\Component;

class LoginAuditReporter extends Component
{
    public function reportSuccessfulLogin(User $user, string $username, array $context = []): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $url = $this->endpointUrl();
        $token = $this->internalToken();
        if ($url === null || $token === null) {
            Yii::warning('Login audit is enabled but endpoint or token is not configured.', 'identity.loginAudit');
            return;
        }

        try {
            $sessionId = (string)($context['session_id'] ?? Yii::$app->security->generateRandomString(32));
            $payload = [
                'eventKey' => 'legacy-login:' . $user->id . ':' . $sessionId,
                'legacyUserId' => (int)$user->id,
                'username' => $username,
                'eventType' => 'login',
                'success' => true,
                'occurredAt' => gmdate('Y-m-d\TH:i:s\Z'),
                'ipAddress' => $context['ip'] ?? null,
                'userAgent' => $context['user_agent'] ?? null,
                'source' => 'legacy-backend',
                'traceId' => $sessionId,
                'metadata' => [
                    'provider' => 'password',
                ],
            ];

            $this->postJson($url, $token, $payload);
        } catch (\Throwable $exception) {
            Yii::warning(
                'Login audit report failed: ' . $exception->getMessage(),
                'identity.loginAudit'
            );
        }
    }

    private function postJson(string $url, string $token, array $payload): void
    {
        if (!function_exists('curl_init')) {
            Yii::warning('Login audit report skipped because curl extension is unavailable.', 'identity.loginAudit');
            return;
        }

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            Yii::warning('Login audit report skipped because payload encoding failed.', 'identity.loginAudit');
            return;
        }

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeoutMs());
        curl_setopt($handle, CURLOPT_TIMEOUT_MS, $this->timeoutMs());
        curl_setopt($handle, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Identity-Internal-Token: ' . $token,
        ]);

        $response = curl_exec($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ($response === false || $status >= 400) {
            Yii::warning(
                'Login audit report was not accepted by identity-service.',
                'identity.loginAudit'
            );
        }
        if ($error !== '') {
            Yii::warning('Login audit transport warning: ' . $error, 'identity.loginAudit');
        }
    }

    private function isEnabled(): bool
    {
        return $this->boolConfig('IDENTITY_LOGIN_AUDIT_ENABLED', false);
    }

    private function endpointUrl(): ?string
    {
        $url = $this->stringConfig('IDENTITY_LOGIN_AUDIT_URL');
        if ($url === null) {
            return null;
        }

        return str_ends_with($url, '/internal/login-events')
            ? $url
            : rtrim($url, '/') . '/internal/login-events';
    }

    private function internalToken(): ?string
    {
        return $this->stringConfig('IDENTITY_LOGIN_AUDIT_TOKEN');
    }

    private function timeoutMs(): int
    {
        return $this->intConfig('IDENTITY_LOGIN_AUDIT_TIMEOUT_MS', 150, 20, 2000);
    }

    private function connectTimeoutMs(): int
    {
        return $this->intConfig('IDENTITY_LOGIN_AUDIT_CONNECT_TIMEOUT_MS', 50, 10, 1000);
    }

    private function boolConfig(string $key, bool $default): bool
    {
        $value = getenv($key);
        if ($value === false && isset(Yii::$app->params['identityLoginAudit'][$key])) {
            $value = Yii::$app->params['identityLoginAudit'][$key];
        }
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string)$value), ['1', 'true', 'yes', 'on'], true);
    }

    private function stringConfig(string $key): ?string
    {
        $value = getenv($key);
        if ($value === false && isset(Yii::$app->params['identityLoginAudit'][$key])) {
            $value = Yii::$app->params['identityLoginAudit'][$key];
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
        if ($value === false && isset(Yii::$app->params['identityLoginAudit'][$key])) {
            $value = Yii::$app->params['identityLoginAudit'][$key];
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT);
        if ($parsed === false) {
            return $default;
        }

        return max($min, min($max, (int)$parsed));
    }
}

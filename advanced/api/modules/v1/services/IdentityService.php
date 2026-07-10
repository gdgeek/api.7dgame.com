<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use api\modules\v1\models\UserLinked;
use api\modules\v1\RefreshToken;
use yii\base\Component;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class IdentityService extends Component
{
    private ?SessionService $sessionService = null;
    private ?LoginAuditReporter $loginAuditReporter = null;
    private ?IdentityProviderClient $identityProviderClient = null;

    public function login($username, $password, array $context = []): array
    {
        if (!$username) {
            throw new BadRequestHttpException("username is required");
        }
        if (!$password) {
            throw new BadRequestHttpException("password is required");
        }

        if ($this->authProvider() === 'identity') {
            return $this->identityProviderClient()->login((string)$username, (string)$password, $context);
        }

        return $this->legacyLogin($username, $password, $context);
    }

    public function refresh($refreshToken, array $context = []): array
    {
        if (!is_string($refreshToken) || $refreshToken === '') {
            throw new BadRequestHttpException("refreshToken is required");
        }

        $normalized = $this->normalizeRefreshTokenInput($refreshToken);
        $refreshToken = $normalized['token'];

        if ($normalized['from_login_code']) {
            $linkedToken = $this->refreshFromLinkedLoginCode($refreshToken, $context);
            if ($linkedToken !== null) {
                return $linkedToken;
            }

            throw new UnauthorizedHttpException('Login code is invalid or expired.');
        }

        if ($this->authProvider() === 'identity') {
            try {
                return $this->identityProviderClient()->refresh($refreshToken, $context);
            } catch (\Throwable $exception) {
                if (!$this->legacyRefreshFallbackEnabled()) {
                    throw $exception;
                }

                \Yii::warning(
                    'Identity refresh failed; trying legacy refresh fallback.',
                    'identity.auth'
                );
            }
        }

        try {
            return $this->legacyRefresh($refreshToken, $context);
        } catch (UnauthorizedHttpException $exception) {
            $linkedToken = $this->refreshFromLinkedLoginCode($refreshToken, $context);
            if ($linkedToken !== null) {
                return $linkedToken;
            }

            throw $exception;
        }
    }

    public function logout(?string $refreshToken): bool
    {
        if ($this->authProvider() === 'identity') {
            try {
                return $this->identityProviderClient()->logout($refreshToken);
            } catch (\Throwable $exception) {
                \Yii::warning('Identity logout failed; falling back to local revoke.', 'identity.auth');
            }
        }

        return $this->sessionService()->revokeRefreshToken($refreshToken);
    }

    public function issueUserToken(User $user, array $context = []): array
    {
        if ($this->authProvider() === 'identity') {
            try {
                return $this->identityProviderClient()->issueUserToken((int)$user->id, $context);
            } catch (\Throwable $exception) {
                if (!$this->legacyRefreshFallbackEnabled()) {
                    throw $exception;
                }

                \Yii::warning(
                    'Identity user token issuance failed; issuing legacy token fallback.',
                    'identity.auth'
                );
            }
        }

        return $this->sessionService()->issueToken($user, $context);
    }

    private function legacyLogin($username, $password, array $context = []): array
    {
        $user = User::findByUsername($username);
        if (!$user) {
            throw new BadRequestHttpException("no user");
        }
        if (!$user->validatePassword($password)) {
            throw new BadRequestHttpException("wrong password");
        }

        $context['session_id'] = $context['session_id'] ?? \Yii::$app->security->generateRandomString(32);
        $token = $this->sessionService()->issueToken($user, $context);
        $this->loginAuditReporter()->reportSuccessfulLogin($user, (string)$username, $context);

        return $token;
    }

    private function legacyRefresh(string $refreshToken, array $context = []): array
    {
        $user = $this->sessionService()->consumeRefreshToken($refreshToken);
        if (!$user instanceof User) {
            throw new BadRequestHttpException("no user");
        }

        if ($user->validate()) {
            $user->save();
        } else {
            throw new BadRequestHttpException("save error");
        }

        return $this->sessionService()->issueToken($user, $context);
    }

    private function normalizeRefreshTokenInput(string $refreshToken): array
    {
        $token = trim($refreshToken);
        $fromLoginCode = false;

        if (preg_match('/(?:^|[?&])web_([^&#\s]+)/', $token, $matches) === 1) {
            $token = $matches[1];
            $fromLoginCode = true;
        } elseif (str_starts_with($token, 'web_')) {
            $token = substr($token, 4);
            $fromLoginCode = true;
        }

        return [
            'token' => $token,
            'from_login_code' => $fromLoginCode,
        ];
    }

    private function refreshFromLinkedLoginCode(string $linkedKey, array $context): ?array
    {
        $linkedKey = trim($linkedKey);
        if ($linkedKey === '') {
            return null;
        }

        $lookupKeys = [$linkedKey];
        $hashedLinkedKey = RefreshToken::hashToken($linkedKey);
        if (!hash_equals($linkedKey, $hashedLinkedKey)) {
            $lookupKeys[] = $hashedLinkedKey;
        }

        $linked = UserLinked::find()->where(['key' => $lookupKeys])->one();
        if (!$linked instanceof UserLinked) {
            return null;
        }

        if ($linked->isLoginCodeExpired()) {
            return null;
        }

        $user = User::findIdentity((int)$linked->user_id);
        if (!$user instanceof User) {
            return null;
        }

        $issuedToken = $this->issueUserToken($user, $context);

        return $issuedToken;
    }
    public function sessionService(): SessionService
    {
        if ($this->sessionService === null) {
            $this->sessionService = new SessionService();
        }

        return $this->sessionService;
    }

    public function loginAuditReporter(): LoginAuditReporter
    {
        if ($this->loginAuditReporter === null) {
            $this->loginAuditReporter = new LoginAuditReporter();
        }

        return $this->loginAuditReporter;
    }

    public function identityProviderClient(): IdentityProviderClient
    {
        if ($this->identityProviderClient === null) {
            $this->identityProviderClient = new IdentityProviderClient();
        }

        return $this->identityProviderClient;
    }

    private function authProvider(): string
    {
        $provider = $this->stringConfig('AUTH_PROVIDER') ?? 'legacy';
        $provider = strtolower($provider);

        return $provider === 'identity' ? 'identity' : 'legacy';
    }

    private function legacyRefreshFallbackEnabled(): bool
    {
        return $this->boolConfig('IDENTITY_AUTH_LEGACY_REFRESH_FALLBACK', true);
    }

    private function stringConfig(string $key): ?string
    {
        $value = getenv($key);
        if ($value === false && isset(\Yii::$app->params['identityAuth'][$key])) {
            $value = \Yii::$app->params['identityAuth'][$key];
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
        if ($value === false && isset(\Yii::$app->params['identityAuth'][$key])) {
            $value = \Yii::$app->params['identityAuth'][$key];
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

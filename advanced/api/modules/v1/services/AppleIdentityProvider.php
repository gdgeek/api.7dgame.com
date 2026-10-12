<?php

namespace api\modules\v1\services;

use common\components\security\ServiceUnavailableHttpException;
use yii\base\Component;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

/** Reuse the existing Apple OAuth provider; Identity owns accounts and bindings. */
class AppleIdentityProvider extends Component
{
    public ?IdentityProviderClient $client = null;

    public function forward(string $action, array $body, array $context = []): array
    {
        $configured = getenv('ENABLE_APPLE_LOGIN');
        $enabled = $configured === false || $configured === ''
            ? !in_array(strtolower(getenv('DEPLOYMENT_MODE') ?: 'cloud'), ['local', 'private'], true)
            : in_array(strtolower($configured), ['1', 'true', 'yes', 'on'], true);
        if (!$enabled) {
            return ['status' => 501, 'body' => ['code' => 'FEATURE_DISABLED', 'feature' => 'apple-login',
                'message' => 'Apple 登录在当前部署模式下已禁用']];
        }
        if ($action === 'apple-id') {
            $appId = $body['data']['userData']['aud'] ?? null;
            $redirect = $body['url'] ?? null;
            $keyEnv = $body['key'] ?? null;
            $code = $body['data']['authorization']['code'] ?? null;
            if (!is_string($appId) || !is_string($redirect) || !is_string($keyEnv)
                || !is_string($code) || $code === '' || strlen($code) > 4096) {
                throw new BadRequestHttpException('Invalid Apple authorization code request.');
            }
            $this->assertAllowed('IDENTITY_APPLE_CLIENT_IDS', $appId);
            $this->assertAllowed('IDENTITY_APPLE_REDIRECT_URIS', $redirect);
            $this->assertAllowed('IDENTITY_APPLE_KEY_ENV_NAMES', $keyEnv);
            if (preg_match('/^[A-Z][A-Z0-9_]{0,127}$/', $keyEnv) !== 1) {
                throw new BadRequestHttpException('Invalid Apple key configuration name.');
            }
            $profile = $this->exchangeCode($code, [
                'clientId' => $appId, 'redirectUri' => $redirect,
                'teamId' => getenv('APPLE_TEAM_ID') ?: '',
                'keyFileId' => getenv($keyEnv) ?: '',
                'keyFilePath' => str_replace('{KEY_ID}', getenv($keyEnv) ?: '', getenv('APPLE_AUTH_KEY') ?: ''),
                'scope' => 'email name',
            ]);
            $claims = $profile['claims'] ?? [];
            if (($claims['iss'] ?? null) !== 'https://appleid.apple.com'
                || ($claims['aud'] ?? null) !== $appId || !is_string($claims['sub'] ?? null)
                || $claims['sub'] === '' || ($profile['subject'] ?? null) !== $claims['sub']
                || !is_int($claims['exp'] ?? null) || $claims['exp'] <= time()
                || !is_int($claims['iat'] ?? null) || $claims['iat'] > time() + 60) {
                throw new UnauthorizedHttpException('Apple identity claims do not match this application.');
            }
            $verifiedEmail = in_array($claims['email_verified'] ?? false, [true, 'true', 1, '1'], true)
                && is_string($claims['email'] ?? null) ? $claims['email'] : null;
            $payload = [
                'providerAppId' => $appId, 'subject' => $claims['sub'], 'email' => $verifiedEmail,
                'emailVerified' => $verifiedEmail !== null,
                'firstName' => $profile['firstName'] ?? null, 'lastName' => $profile['lastName'] ?? null,
                'privateEmail' => in_array($claims['is_private_email'] ?? false, [true, 'true', 1, '1'], true),
            ];
            return $this->provider()->verifiedApple('resolve', $payload, $context);
        }
        $operation = ['apple-id-create' => 'create', 'apple-id-link' => 'link'][$action] ?? null;
        if ($operation === null) {
            throw new BadRequestHttpException('Unknown Apple account action.');
        }
        // The client supplies a short Identity challenge here, never verified
        // provider claims. Identity validates the challenge and credentials.
        return $this->provider()->verifiedApple($operation, array_intersect_key($body,
            array_flip(['apple_id', 'token', 'username', 'password', 'providerAppId'])), $context);
    }

    protected function exchangeCode(string $code, array $configuration): array
    {
        if (!class_exists(\Firebase\JWT\JWT::class) || !class_exists(\Firebase\JWT\JWK::class)) {
            throw new ServiceUnavailableHttpException('Apple OAuth provider dependencies are not installed.');
        }
        if ($configuration['teamId'] === '' || $configuration['keyFileId'] === ''
            || $configuration['keyFilePath'] === '' || !is_readable($configuration['keyFilePath'])) {
            throw new ServiceUnavailableHttpException('Apple OAuth provider credentials are not configured.');
        }
        $previousLeeway = \Firebase\JWT\JWT::$leeway;
        \Firebase\JWT\JWT::$leeway = 60;
        try {
            $provider = $this->createOAuthProvider($configuration);
            $token = $provider->getAccessToken('authorization_code', ['code' => $code]);
            // Verify explicitly even when an Apple response has no refresh
            // token; older SDK versions conditionally skip id_token decoding.
            $jwks = $this->fetchAppleJwks($provider);
            $claims = (array)\Firebase\JWT\JWT::decode($token->getIdToken(), \Firebase\JWT\JWK::parseKeySet($jwks));
            $owner = $provider->getResourceOwner($token);
            return ['claims' => $claims, 'subject' => $owner->getId(),
                'firstName' => $owner->getFirstName(), 'lastName' => $owner->getLastName()];
        } catch (ServiceUnavailableHttpException $exception) {
            throw $exception;
        } catch (\GuzzleHttp\Exception\ConnectException | \GuzzleHttp\Exception\ServerException $exception) {
            throw new ServiceUnavailableHttpException('Apple OAuth provider is unavailable.');
        } catch (\Throwable $exception) {
            // Provider exception bodies may contain authorization secrets.
            throw new UnauthorizedHttpException('Apple rejected the authorization code.');
        } finally {
            \Firebase\JWT\JWT::$leeway = $previousLeeway;
        }
    }

    protected function createOAuthProvider(array $configuration): object
    {
        if (!class_exists(\League\OAuth2\Client\Provider\Apple::class)) {
            throw new ServiceUnavailableHttpException('Apple OAuth provider dependency is not installed.');
        }
        return new \League\OAuth2\Client\Provider\Apple($configuration, [
            'httpClient' => new \GuzzleHttp\Client(['timeout' => 5, 'connect_timeout' => 2]),
        ]);
    }

    protected function fetchAppleJwks(object $provider): array
    {
        $keys = $provider->getHttpClient()->request('GET', 'https://appleid.apple.com/auth/keys');
        $jwks = json_decode((string)$keys->getBody(), true);
        if (!is_array($jwks) || !is_array($jwks['keys'] ?? null)) {
            throw new ServiceUnavailableHttpException('Apple signing keys are unavailable.');
        }
        return $jwks;
    }

    private function assertAllowed(string $name, string $value): void
    {
        $configured = getenv($name);
        $allowed = $configured === false ? [] : array_values(array_filter(array_map('trim', explode(',', $configured))));
        if ($allowed === []) {
            throw new ServiceUnavailableHttpException($name . ' is required for Apple login.');
        }
        if (!in_array($value, $allowed, true)) {
            throw new UnauthorizedHttpException('Apple application configuration is not allowed.');
        }
    }

    private function provider(): IdentityProviderClient
    {
        return $this->client ??= new IdentityProviderClient();
    }
}

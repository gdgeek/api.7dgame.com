<?php

declare(strict_types=1);

/**
 * Test-only verifier and exact cleanup companion for the isolated main-API
 * HTTP token leg. It accepts bearer-equivalent values only on STDIN and emits
 * no token, code, digest, Redis key, database setting, or exception detail.
 *
 * This file is deliberately CLI-only. The Node runner independently accepts
 * only xrugc-test-* Docker services; this in-process gate prevents accidental
 * direct use against a non-test runtime.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '0');
ob_start();

/** @param array<string, mixed> $payload */
function qrLoginCodeMainTokenProbeEmit(array $payload, int $status = 0): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    exit($status);
}

function qrLoginCodeMainTokenProbeAssertIsolatedEnvironment(): void
{
    $expected = [
        'DEPLOYMENT_MODE' => 'test',
        'REDIS_HOST' => 'redis',
        'REDIS_PORT' => '6379',
        'REDIS_DB' => '15',
        'LOGIN_CODE_READ_MODE' => 'redis',
        'LOGIN_CODE_WRITE_MODE' => 'redis',
        'LOGIN_CODE_LEGACY_DB_AVAILABLE' => 'false',
        'MYSQL_HOST' => 'db',
    ];
    foreach ($expected as $name => $value) {
        if (getenv($name) !== $value) {
            throw new RuntimeException('This probe only runs in the isolated Redis-only test environment.');
        }
    }

    $provider = getenv('AUTH_PROVIDER');
    if ($provider !== false && $provider !== '' && strtolower((string)$provider) !== 'legacy') {
        throw new RuntimeException('This probe requires the isolated legacy token issuer.');
    }
}

/**
 * @return array{action: string, user_id?: int, tokens: list<array{access_token: string, refresh_token: string}>}
 */
function qrLoginCodeMainTokenProbeInput(): array
{
    $decoded = json_decode(stream_get_contents(STDIN), true, 16, JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid probe input.');
    }

    $action = $decoded['action'] ?? null;
    if (!is_string($action) || !in_array($action, ['verify_tokens', 'delete_refresh_tokens'], true)) {
        throw new RuntimeException('Invalid probe action.');
    }

    $tokens = $decoded['tokens'] ?? null;
    if (!is_array($tokens) || count($tokens) > 4 || ($action === 'verify_tokens' && count($tokens) < 1)) {
        throw new RuntimeException('Invalid probe tokens.');
    }

    $normalizedTokens = [];
    foreach ($tokens as $token) {
        if (!is_array($token)) {
            throw new RuntimeException('Invalid probe token.');
        }
        $accessToken = $token['access_token'] ?? null;
        $refreshToken = $token['refresh_token'] ?? null;
        if (
            !is_string($accessToken)
            || strlen($accessToken) < 32
            || strlen($accessToken) > 4096
            || preg_match('/\s/D', $accessToken) === 1
            || !is_string($refreshToken)
            || strlen($refreshToken) < 32
            || strlen($refreshToken) > 512
            || preg_match('/^[A-Za-z0-9_-]+$/D', $refreshToken) !== 1
        ) {
            throw new RuntimeException('Invalid probe token.');
        }
        $normalizedTokens[] = [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    $result = [
        'action' => $action,
        'tokens' => $normalizedTokens,
    ];
    if ($action === 'verify_tokens') {
        $userId = $decoded['user_id'] ?? null;
        if (!is_int($userId) || $userId < 100000000 || $userId > 2000000000) {
            throw new RuntimeException('Invalid probe user.');
        }
        $result['user_id'] = $userId;
    }

    return $result;
}

/** @param list<array{access_token: string, refresh_token: string}> $tokens */
function qrLoginCodeMainTokenProbeDeleteRefreshTokens(
    api\modules\v1\services\SessionService $sessionService,
    array $tokens,
): void {
    foreach ($tokens as $token) {
        try {
            $record = $sessionService->findRefreshTokenRecord($token['refresh_token']);
            if ($record instanceof api\modules\v1\RefreshToken) {
                $record->delete();
            }
        } catch (Throwable) {
            // Preserve the original verifier error and never reveal a
            // refresh token in a cleanup diagnostic.
        }
    }
}

/** @param list<array{access_token: string, refresh_token: string}> $tokens */
function qrLoginCodeMainTokenProbeVerifyTokens(
    int $userId,
    api\modules\v1\services\SessionService $sessionService,
    array $tokens,
): void {
    foreach ($tokens as $token) {
        if (api\modules\v1\models\User::tokenToId($token['access_token']) !== $userId) {
            throw new RuntimeException('Unexpected access token.');
        }

        $record = $sessionService->findRefreshTokenRecord($token['refresh_token']);
        if (!$record instanceof api\modules\v1\RefreshToken || (int)$record->user_id !== $userId) {
            throw new RuntimeException('Unexpected refresh token.');
        }
    }
}

try {
    qrLoginCodeMainTokenProbeAssertIsolatedEnvironment();
    $input = qrLoginCodeMainTokenProbeInput();
    $basePath = dirname(__DIR__, 2);

    defined('YII_DEBUG') or define('YII_DEBUG', false);
    defined('YII_ENV') or define('YII_ENV', 'test');
    require_once $basePath . '/vendor/autoload.php';
    require_once $basePath . '/vendor/yiisoft/yii2/Yii.php';
    require_once $basePath . '/common/config/bootstrap.php';
    require_once $basePath . '/api/config/bootstrap.php';

    $config = yii\helpers\ArrayHelper::merge(
        require $basePath . '/common/config/main.php',
        require $basePath . '/common/config/main-local.php',
        require $basePath . '/api/config/main.php',
        require $basePath . '/api/config/main-local.php',
    );
    new yii\web\Application($config);

    $sessionService = new api\modules\v1\services\SessionService();
    if ($input['action'] === 'verify_tokens') {
        try {
            qrLoginCodeMainTokenProbeVerifyTokens($input['user_id'], $sessionService, $input['tokens']);
        } finally {
            qrLoginCodeMainTokenProbeDeleteRefreshTokens($sessionService, $input['tokens']);
        }
        qrLoginCodeMainTokenProbeEmit(['ok' => true, 'operation' => 'verify_tokens']);
    }

    qrLoginCodeMainTokenProbeDeleteRefreshTokens($sessionService, $input['tokens']);
    qrLoginCodeMainTokenProbeEmit(['ok' => true, 'operation' => 'delete_refresh_tokens']);
} catch (Throwable) {
    qrLoginCodeMainTokenProbeEmit(['ok' => false, 'error' => 'probe_failed'], 1);
}

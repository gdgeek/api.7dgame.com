<?php

declare(strict_types=1);

/**
 * Test-only main-API companion for the isolated redis-first fallback harness.
 *
 * It accepts a bearer code only on STDIN, emits only categorical outcomes, and
 * refuses every environment except the local Docker test stack configured for
 * redis-first/redis on Redis logical database 15. This is intentionally a CLI
 * probe, not an HTTP route.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '0');
ob_start();

/** @param array<string, mixed> $payload */
function qrLoginCodeRedisFirstMainEmit(array $payload, int $status = 0): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    exit($status);
}

/**
 * @return array{action: string, transport: string, code: string, user_id?: int}
 */
function qrLoginCodeRedisFirstMainInput(): array
{
    $decoded = json_decode(stream_get_contents(STDIN), true, 16, JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid probe input.');
    }

    $action = $decoded['action'] ?? null;
    $transport = $decoded['transport'] ?? 'live';
    $code = $decoded['code'] ?? null;
    if (
        !is_string($action)
        || !in_array($action, ['issue', 'resolve', 'status', 'cleanup'], true)
        || !is_string($transport)
        || !in_array($transport, ['live', 'unavailable'], true)
        || !is_string($code)
        || preg_match('/^[A-Za-z0-9_-]{64}$/D', $code) !== 1
        || str_starts_with($code, 'web_')
    ) {
        throw new RuntimeException('Invalid probe input.');
    }

    if (in_array($action, ['issue', 'cleanup'], true) && $transport !== 'live') {
        throw new RuntimeException('Invalid probe transport.');
    }

    $result = [
        'action' => $action,
        'transport' => $transport,
        'code' => $code,
    ];
    if (in_array($action, ['issue', 'status'], true)) {
        $userId = $decoded['user_id'] ?? null;
        if (!is_int($userId) || $userId < 100000000 || $userId > 2000000000) {
            throw new RuntimeException('Invalid probe user.');
        }
        $result['user_id'] = $userId;
    }

    return $result;
}

function qrLoginCodeRedisFirstMainAssertIsolatedEnvironment(): void
{
    // The Node runner additionally verifies Docker labels and networking.
    // Keep an independent in-container allowlist so a copied source tree can
    // never use this probe against a normal dev or production service.
    $expected = [
        'DEPLOYMENT_MODE' => 'test',
        'REDIS_HOST' => 'redis',
        'REDIS_PORT' => '6379',
        'REDIS_DB' => '15',
        'LOGIN_CODE_READ_MODE' => 'redis-first',
        'LOGIN_CODE_WRITE_MODE' => 'redis',
        'LOGIN_CODE_LEGACY_DB_AVAILABLE' => 'true',
        'MYSQL_HOST' => 'db',
    ];
    foreach ($expected as $name => $value) {
        if (getenv($name) !== $value) {
            throw new RuntimeException('This probe only runs in the isolated redis-first test environment.');
        }
    }

    $fingerprint = getenv('LOGIN_CODE_PROTOCOL_FINGERPRINT');
    if (!is_string($fingerprint) || !hash_equals(
        api\modules\v1\services\LoginCodeSettings::defaultProtocolFingerprint(),
        $fingerprint,
    )) {
        throw new RuntimeException('The isolated test protocol fingerprint is invalid.');
    }
}

/** @return yii\db\Connection */
function qrLoginCodeRedisFirstMainDatabase(): yii\db\Connection
{
    $database = new yii\db\Connection([
        'dsn' => 'mysql:host=' . (string) getenv('MYSQL_HOST')
            . ';dbname=' . (string) getenv('MYSQL_DB')
            . ';charset=utf8mb4',
        'username' => (string) getenv('MYSQL_USERNAME'),
        'password' => (string) getenv('MYSQL_PASSWORD'),
        'enableLogging' => false,
        'enableProfiling' => false,
    ]);

    return $database;
}

/** @return yii\redis\Connection */
function qrLoginCodeRedisFirstMainRedis(string $transport): yii\redis\Connection
{
    if ($transport === 'unavailable') {
        // Loopback port 1 has no service in the isolated API container. This
        // exercises the production Yii Redis client/readiness failure path
        // without stopping or reconfiguring the shared test Redis service.
        return new yii\redis\Connection([
            'hostname' => '127.0.0.1',
            'port' => 1,
            'database' => 15,
            'connectionTimeout' => 0.2,
            'dataTimeout' => 0.2,
        ]);
    }

    return new yii\redis\Connection([
        'hostname' => 'redis',
        'port' => 6379,
        'database' => 15,
    ]);
}

try {
    $input = qrLoginCodeRedisFirstMainInput();
    $basePath = dirname(__DIR__, 2);

    defined('YII_DEBUG') or define('YII_DEBUG', false);
    defined('YII_ENV') or define('YII_ENV', 'test');
    require_once $basePath . '/vendor/autoload.php';
    require_once $basePath . '/vendor/yiisoft/yii2/Yii.php';
    require_once $basePath . '/common/config/bootstrap.php';

    qrLoginCodeRedisFirstMainAssertIsolatedEnvironment();
    $database = qrLoginCodeRedisFirstMainDatabase();
    $redis = qrLoginCodeRedisFirstMainRedis($input['transport']);
    new yii\console\Application([
        'id' => 'qr-login-code-redis-first-main-probe',
        'basePath' => $basePath,
        'runtimePath' => sys_get_temp_dir() . '/qr-login-code-redis-first-main-probe',
        'components' => [
            'db' => $database,
            'redis' => $redis,
        ],
    ]);

    $settings = new api\modules\v1\services\LoginCodeSettings([
        'readMode' => (string) getenv('LOGIN_CODE_READ_MODE'),
        'writeMode' => (string) getenv('LOGIN_CODE_WRITE_MODE'),
        'prefix' => (string) getenv('LOGIN_CODE_REDIS_PREFIX'),
        'protocolFingerprint' => (string) getenv('LOGIN_CODE_PROTOCOL_FINGERPRINT'),
        'issueLimit' => (string) getenv('LOGIN_CODE_ISSUE_LIMIT'),
        'activeWindowSeconds' => (string) getenv('LOGIN_CODE_ACTIVE_WINDOW_SECONDS'),
        'recordRetentionSeconds' => (string) getenv('LOGIN_CODE_RECORD_TTL_SECONDS'),
        'issueWindowSeconds' => (string) getenv('LOGIN_CODE_ISSUE_WINDOW_SECONDS'),
        'legacyDbAvailable' => (string) getenv('LOGIN_CODE_LEGACY_DB_AVAILABLE'),
    ]);
    $readiness = new api\modules\v1\services\LoginCodeReadiness([
        'settings' => $settings,
        'redis' => $redis,
        'db' => $database,
    ]);
    $store = new api\modules\v1\services\LoginCodeStore(
        $redis,
        $settings,
        $readiness,
        static fn (): string => $input['code'],
    );

    if ($input['action'] === 'issue') {
        $issued = $store->issue($input['user_id'], ['source' => 'redis-first-harness']);
        if (($issued['expires_in'] ?? null) !== api\modules\v1\services\LoginCodeSettings::ACTIVE_WINDOW_SECONDS) {
            throw new RuntimeException('Unexpected issue result.');
        }
        qrLoginCodeRedisFirstMainEmit([
            'ok' => true,
            'service' => 'main-api-store',
            'operation' => 'issue',
            'expires_in' => api\modules\v1\services\LoginCodeSettings::ACTIVE_WINDOW_SECONDS,
        ]);
    }

    if ($input['action'] === 'cleanup') {
        $store->deleteExact($input['code']);
        qrLoginCodeRedisFirstMainEmit([
            'ok' => true,
            'service' => 'main-api-store',
            'operation' => 'cleanup',
        ]);
    }

    if ($input['action'] === 'status') {
        try {
            $status = $store->status($input['user_id'], $input['code']);
        } catch (common\components\security\ServiceUnavailableHttpException) {
            qrLoginCodeRedisFirstMainEmit([
                'ok' => true,
                'service' => 'main-api-status',
                'outcome' => 'service_unavailable',
            ]);
        }

        $reason = $status['reason'] ?? null;
        if (!is_string($reason) || !in_array($reason, ['active', 'expired', 'not_found'], true)) {
            throw new RuntimeException('Unexpected status result.');
        }
        qrLoginCodeRedisFirstMainEmit([
            'ok' => true,
            'service' => 'main-api-status',
            'outcome' => $reason,
        ]);
    }

    try {
        $result = $store->resolve($input['code']);
    } catch (common\components\security\ServiceUnavailableHttpException) {
        qrLoginCodeRedisFirstMainEmit([
            'ok' => true,
            'service' => 'main-api-store',
            'outcome' => 'service_unavailable',
        ]);
    }

    $outcome = $result['outcome'] ?? null;
    if (!is_string($outcome) || !in_array($outcome, ['hit', 'expired', 'miss'], true)) {
        throw new RuntimeException('Unexpected resolve result.');
    }
    qrLoginCodeRedisFirstMainEmit([
        'ok' => true,
        'service' => 'main-api-store',
        'outcome' => $outcome,
    ]);
} catch (Throwable) {
    // The input includes a bearer-equivalent code. Keep all errors redacted.
    qrLoginCodeRedisFirstMainEmit([
        'ok' => false,
        'error' => 'probe_failed',
    ], 1);
}

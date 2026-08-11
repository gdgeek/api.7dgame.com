<?php

declare(strict_types=1);

/**
 * Test-only main-API resolver companion for the isolated real-Redis
 * login-code time/TTL boundary harness.
 *
 * It accepts a bearer code only on STDIN and emits a redacted outcome. This is
 * deliberately a CLI probe rather than an HTTP route, and it refuses every
 * environment except the Docker test stack's Redis database 15.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '0');
ob_start();

/** @param array<string, mixed> $payload */
function qrLoginCodeRedisBoundaryMainEmit(array $payload, int $status = 0): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    exit($status);
}

/** @return array{action: string, code: string, user_id?: int} */
function qrLoginCodeRedisBoundaryMainInput(): array
{
    $decoded = json_decode(stream_get_contents(STDIN), true, 8, JSON_THROW_ON_ERROR);
    $code = is_array($decoded) ? ($decoded['code'] ?? null) : null;
    if (
        !is_string($code)
        || preg_match('/^[A-Za-z0-9_-]{64}$/D', $code) !== 1
        || str_starts_with($code, 'web_')
    ) {
        throw new RuntimeException('Invalid probe input.');
    }

    $action = $decoded['action'] ?? 'resolve';
    if (!is_string($action) || !in_array($action, ['resolve', 'status'], true)) {
        throw new RuntimeException('Invalid probe action.');
    }

    $input = [
        'action' => $action,
        'code' => $code,
    ];
    if ($action === 'status') {
        $userId = $decoded['user_id'] ?? null;
        if (!is_int($userId) || $userId <= 0) {
            throw new RuntimeException('Invalid probe user.');
        }
        $input['user_id'] = $userId;
    }

    return $input;
}

function qrLoginCodeRedisBoundaryMainAssertIsolatedTestEnvironment(): void
{
    // A source mount can be reused by other containers. The runner validates
    // Docker labels/networking too; keep this in-probe guard as a second line
    // of defence against accidentally targeting a non-test Redis instance.
    $expected = [
        'DEPLOYMENT_MODE' => 'test',
        'REDIS_HOST' => 'redis',
        'REDIS_PORT' => '6379',
        'REDIS_DB' => '15',
    ];
    foreach ($expected as $name => $value) {
        if (getenv($name) !== $value) {
            throw new RuntimeException('This probe only runs in the isolated test environment.');
        }
    }
}

try {
    $input = qrLoginCodeRedisBoundaryMainInput();
    qrLoginCodeRedisBoundaryMainAssertIsolatedTestEnvironment();
    $basePath = dirname(__DIR__, 2);

    require_once $basePath . '/vendor/autoload.php';
    require_once $basePath . '/vendor/yiisoft/yii2/Yii.php';
    require_once $basePath . '/common/config/bootstrap.php';

    // Keep telemetry and readiness execution real without exposing an HTTP
    // endpoint or loading a production application configuration.
    new yii\console\Application([
        'id' => 'qr-login-code-redis-boundary-main-probe',
        'basePath' => $basePath,
        'runtimePath' => sys_get_temp_dir() . '/qr-login-code-redis-boundary-main-probe',
    ]);

    $settings = new api\modules\v1\services\LoginCodeSettings([
        'readMode' => api\modules\v1\services\LoginCodeSettings::READ_REDIS,
        'writeMode' => api\modules\v1\services\LoginCodeSettings::WRITE_REDIS,
        'prefix' => 'auth:login-code:v1',
        'protocolFingerprint' => api\modules\v1\services\LoginCodeSettings::defaultProtocolFingerprint(),
        'legacyDbAvailable' => false,
    ]);
    $redis = new yii\redis\Connection([
        'hostname' => 'redis',
        'port' => 6379,
        'database' => 15,
    ]);
    $readiness = new api\modules\v1\services\LoginCodeReadiness([
        'settings' => $settings,
        'redis' => $redis,
    ]);
    $store = new api\modules\v1\services\LoginCodeStore($redis, $settings, $readiness);
    if ($input['action'] === 'status') {
        $result = $store->status($input['user_id'], $input['code']);
        $reason = $result['reason'] ?? null;
        if (!is_string($reason) || !in_array($reason, ['active', 'expired', 'not_found'], true)) {
            throw new RuntimeException('Unexpected status result.');
        }

        qrLoginCodeRedisBoundaryMainEmit([
            'ok' => true,
            'service' => 'main-api-status',
            'reason' => $reason,
            'active' => ($result['active'] ?? false) === true,
        ]);
    }

    $result = $store->resolve($input['code']);
    $outcome = $result['outcome'] ?? null;
    if (!is_string($outcome) || !in_array($outcome, ['hit', 'expired', 'miss'], true)) {
        throw new RuntimeException('Unexpected resolve result.');
    }

    // No user id, code, digest, payload, Redis configuration, or exception
    // detail leaves this process.
    qrLoginCodeRedisBoundaryMainEmit([
        'ok' => true,
        'service' => 'main-api-store',
        'outcome' => $outcome,
    ]);
} catch (Throwable) {
    qrLoginCodeRedisBoundaryMainEmit([
        'ok' => false,
        'error' => 'probe_failed',
    ], 1);
}

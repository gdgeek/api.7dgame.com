<?php

declare(strict_types=1);

/**
 * Test-only interactive worker for concurrent QR login-code issue tests.
 *
 * A Node runner starts several independent CLI workers at once. Each worker
 * builds the real Redis-only main-API Store, reports an aggregate "ready"
 * marker, then waits for the runner to release all workers together. The raw
 * bearer code appears only in the first STDIN line and is never echoed.
 *
 * This probe never creates a database connection and never references
 * UserLinked/user_linked.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '0');
ob_start();

/** @param array<string, mixed> $payload */
function qrLoginCodeConcurrentIssueEmit(array $payload, int $status = 0): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    fflush(STDOUT);
    exit($status);
}

/** @param array<string, mixed> $payload */
function qrLoginCodeConcurrentIssueEmitLine(array $payload): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    fflush(STDOUT);
}

/** @return array{user_id: int, code: string} */
function qrLoginCodeConcurrentIssueInput(): array
{
    $line = fgets(STDIN);
    $decoded = is_string($line) ? json_decode($line, true, 16, JSON_THROW_ON_ERROR) : null;
    if (
        !is_array($decoded)
        || ($decoded['action'] ?? null) !== 'ready_then_issue'
        || !isset($decoded['user_id'], $decoded['code'])
        || !is_int($decoded['user_id'])
        || $decoded['user_id'] <= 0
        || !is_string($decoded['code'])
        || preg_match('/^[A-Za-z0-9_-]{64}$/D', $decoded['code']) !== 1
        || str_starts_with($decoded['code'], 'web_')
    ) {
        throw new RuntimeException('Invalid probe input.');
    }

    return [
        'user_id' => $decoded['user_id'],
        'code' => $decoded['code'],
    ];
}

function qrLoginCodeConcurrentIssueAssertIsolatedTestEnvironment(): void
{
    // A source mount can make this file visible outside the test container, so
    // keep the in-process gate at least as strict as the Node runner.
    $expected = [
        'DEPLOYMENT_MODE' => 'test',
        'REDIS_HOST' => 'redis',
        'REDIS_PORT' => '6379',
        'REDIS_DB' => '15',
        'LOGIN_CODE_READ_MODE' => 'redis',
        'LOGIN_CODE_WRITE_MODE' => 'redis',
        'LOGIN_CODE_LEGACY_DB_AVAILABLE' => 'false',
    ];
    foreach ($expected as $name => $value) {
        if (getenv($name) !== $value) {
            throw new RuntimeException('This probe only runs in the isolated Redis-only test environment.');
        }
    }
}

/** @return api\modules\v1\services\LoginCodeStore */
function qrLoginCodeConcurrentIssueStore(string $rawCode): api\modules\v1\services\LoginCodeStore
{
    $basePath = dirname(__DIR__, 2);
    require_once $basePath . '/vendor/autoload.php';
    require_once $basePath . '/vendor/yiisoft/yii2/Yii.php';
    require_once $basePath . '/common/config/bootstrap.php';

    // LoginCodeTelemetry uses Yii's logger. Each independent CLI worker gets
    // a minimal application; no HTTP route, database component, or legacy
    // model is loaded or invoked in Redis-only mode.
    new yii\console\Application([
        'id' => 'qr-login-code-concurrent-issue-probe',
        'basePath' => $basePath,
        'runtimePath' => sys_get_temp_dir() . '/qr-login-code-concurrent-issue-probe',
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

    return new api\modules\v1\services\LoginCodeStore(
        $redis,
        $settings,
        $readiness,
        static fn (): string => $rawCode,
    );
}

try {
    qrLoginCodeConcurrentIssueAssertIsolatedTestEnvironment();
    $input = qrLoginCodeConcurrentIssueInput();
    $store = qrLoginCodeConcurrentIssueStore($input['code']);

    // The runner only sends G after every separate worker has reached this
    // point. All workers then enter LoginCodeStore::issue() concurrently.
    qrLoginCodeConcurrentIssueEmitLine([
        'ok' => true,
        'operation' => 'ready',
    ]);
    if (trim((string) fgets(STDIN)) !== 'G') {
        throw new RuntimeException('Concurrent issue release was not received.');
    }

    $issued = $store->issue($input['user_id'], ['source' => 'concurrency-harness']);
    if (
        !isset($issued['key'], $issued['expires_in'])
        || !is_string($issued['key'])
        || !hash_equals($input['code'], $issued['key'])
        || (int)$issued['expires_in'] !== api\modules\v1\services\LoginCodeSettings::ACTIVE_WINDOW_SECONDS
    ) {
        throw new RuntimeException('Unexpected concurrent issue result.');
    }

    qrLoginCodeConcurrentIssueEmit([
        'ok' => true,
        'operation' => 'issued',
        'expires_in' => api\modules\v1\services\LoginCodeSettings::ACTIVE_WINDOW_SECONDS,
    ]);
} catch (Throwable) {
    qrLoginCodeConcurrentIssueEmit([
        'ok' => false,
        'error' => 'probe_failed',
    ], 1);
}

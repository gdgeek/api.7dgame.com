<?php

declare(strict_types=1);

/**
 * Test-only companion for tools/identity/test-qr-login-code-cross-service.mjs.
 *
 * This is deliberately a CLI probe, not an HTTP endpoint. It is copied only
 * into the isolated xrugc-test-api source mount and accepts its bearer code on
 * STDIN so credentials never appear in the command line or probe output.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '0');
ob_start();

/** @param array<string, mixed> $payload */
function qrLoginCodeCrossServiceIssueEmit(array $payload, int $status = 0): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    exit($status);
}

/** @return array{action: string, code: string, user_id?: int} */
function qrLoginCodeCrossServiceIssueInput(): array
{
    $decoded = json_decode(stream_get_contents(STDIN), true, 16, JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid probe input.');
    }

    $action = $decoded['action'] ?? null;
    $code = $decoded['code'] ?? null;
    if (!is_string($action) || !in_array($action, ['issue', 'cleanup'], true)) {
        throw new RuntimeException('Invalid probe action.');
    }

    if (
        !is_string($code)
        || preg_match('/^[A-Za-z0-9_-]{64}$/D', $code) !== 1
        || str_starts_with($code, 'web_')
    ) {
        throw new RuntimeException('Invalid probe code.');
    }

    $result = [
        'action' => $action,
        'code' => $code,
    ];

    if ($action === 'issue') {
        $userId = $decoded['user_id'] ?? null;
        if (!is_int($userId) || $userId <= 0) {
            throw new RuntimeException('Invalid probe user.');
        }

        $result['user_id'] = $userId;
    }

    return $result;
}

function qrLoginCodeCrossServiceIssueAssertIsolatedTestEnvironment(): void
{
    // This file can exist in a source tree used by more than one container.
    // A direct CLI invocation must still be unable to touch a non-test Redis.
    if (getenv('DEPLOYMENT_MODE') !== 'test' || getenv('REDIS_DB') !== '15') {
        throw new RuntimeException('This probe only runs in the isolated test environment.');
    }
}

try {
    $input = qrLoginCodeCrossServiceIssueInput();
    qrLoginCodeCrossServiceIssueAssertIsolatedTestEnvironment();
    $basePath = dirname(__DIR__, 2);

    require_once $basePath . '/vendor/autoload.php';
    require_once $basePath . '/vendor/yiisoft/yii2/Yii.php';
    require_once $basePath . '/common/config/bootstrap.php';

    // LoginCodeTelemetry intentionally uses Yii's logger. A minimal CLI
    // application keeps that behavior real without loading HTTP routes,
    // production config, database credentials, or an external service.
    new yii\console\Application([
        'id' => 'qr-login-code-cross-service-probe',
        'basePath' => $basePath,
        'runtimePath' => sys_get_temp_dir() . '/qr-login-code-cross-service-probe',
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
    $store = new api\modules\v1\services\LoginCodeStore(
        $redis,
        $settings,
        $readiness,
        static fn (): string => $input['code'],
    );

    if ($input['action'] === 'cleanup') {
        $store->deleteExact($input['code']);
        qrLoginCodeCrossServiceIssueEmit([
            'ok' => true,
            'operation' => 'cleanup',
        ]);
    }

    $issued = $store->issue($input['user_id'], [
        'source' => 'cross-service-harness',
    ]);
    if (
        !isset($issued['key'], $issued['expires_in'])
        || !is_string($issued['key'])
        || !hash_equals($input['code'], $issued['key'])
        || (int)$issued['expires_in'] !== api\modules\v1\services\LoginCodeSettings::ACTIVE_WINDOW_SECONDS
    ) {
        throw new RuntimeException('Unexpected issue result.');
    }

    qrLoginCodeCrossServiceIssueEmit([
        'ok' => true,
        'operation' => 'issue',
        'expires_in' => api\modules\v1\services\LoginCodeSettings::ACTIVE_WINDOW_SECONDS,
    ]);
} catch (Throwable) {
    // Keep failures redacted: inputs contain a bearer credential.
    qrLoginCodeCrossServiceIssueEmit([
        'ok' => false,
        'error' => 'probe_failed',
    ], 1);
}

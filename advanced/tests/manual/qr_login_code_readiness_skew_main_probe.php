<?php

declare(strict_types=1);

/**
 * Test-only deterministic readiness-gate probe for the isolated QR login-code
 * Docker stack. The runner supplies a Redis TIME value sampled from the
 * isolated Redis instance and an app-clock offset; this probe never opens a
 * Redis or database connection and never accepts a credential.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '0');
ob_start();

/** @param array<string, mixed> $payload */
function qrLoginCodeReadinessSkewMainEmit(array $payload, int $status = 0): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode($payload, JSON_THROW_ON_ERROR), PHP_EOL;
    exit($status);
}

/** @return array{redis_time_milliseconds: int, app_offset_milliseconds: int} */
function qrLoginCodeReadinessSkewMainInput(): array
{
    $decoded = json_decode(stream_get_contents(STDIN), true, 8, JSON_THROW_ON_ERROR);
    $redisTimeMilliseconds = is_array($decoded) ? ($decoded['redis_time_milliseconds'] ?? null) : null;
    $appOffsetMilliseconds = is_array($decoded) ? ($decoded['app_offset_milliseconds'] ?? null) : null;

    if (
        !is_int($redisTimeMilliseconds)
        || $redisTimeMilliseconds < 1_000_000_000_000
        || $redisTimeMilliseconds > 4_102_444_800_000
        || !is_int($appOffsetMilliseconds)
        || $appOffsetMilliseconds < -2_000
        || $appOffsetMilliseconds > 2_000
    ) {
        throw new RuntimeException('Invalid probe input.');
    }

    return [
        'redis_time_milliseconds' => $redisTimeMilliseconds,
        'app_offset_milliseconds' => $appOffsetMilliseconds,
    ];
}

function qrLoginCodeReadinessSkewMainAssertIsolatedTestEnvironment(): void
{
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

try {
    $input = qrLoginCodeReadinessSkewMainInput();
    qrLoginCodeReadinessSkewMainAssertIsolatedTestEnvironment();
    $basePath = dirname(__DIR__, 2);

    require_once $basePath . '/vendor/autoload.php';
    require_once $basePath . '/vendor/yiisoft/yii2/Yii.php';
    require_once $basePath . '/common/config/bootstrap.php';

    new yii\console\Application([
        'id' => 'qr-login-code-readiness-skew-main-probe',
        'basePath' => $basePath,
        'runtimePath' => sys_get_temp_dir() . '/qr-login-code-readiness-skew-main-probe',
    ]);

    $redis = new class($input['redis_time_milliseconds']) {
        public int $database = 15;

        public function __construct(private readonly int $timeMilliseconds)
        {
        }

        /** @param array<int, mixed> $arguments */
        public function executeCommand(string $command, array $arguments): mixed
        {
            return match (strtoupper($command)) {
                'TIME' => [
                    (string) intdiv($this->timeMilliseconds, 1000),
                    (string) (($this->timeMilliseconds % 1000) * 1000),
                ],
                'GET' => null,
                'PTTL' => -2,
                default => throw new RuntimeException('Unexpected probe Redis command.'),
            };
        }
    };
    $settings = new api\modules\v1\services\LoginCodeSettings([
        'readMode' => api\modules\v1\services\LoginCodeSettings::READ_REDIS,
        'writeMode' => api\modules\v1\services\LoginCodeSettings::WRITE_REDIS,
        'prefix' => 'auth:login-code:v1',
        'protocolFingerprint' => api\modules\v1\services\LoginCodeSettings::defaultProtocolFingerprint(),
        'legacyDbAvailable' => false,
    ]);
    $readiness = new api\modules\v1\services\LoginCodeReadiness([
        'settings' => $settings,
        'redis' => $redis,
        'clock' => static fn (): int => $input['redis_time_milliseconds'] + $input['app_offset_milliseconds'],
    ]);
    $gate = $readiness->check();
    $ready = ($gate['status'] ?? null) === 'up';
    $reason = $ready ? 'ready' : ($gate['error'] ?? null);
    if (!is_string($reason) || !in_array($reason, ['ready', 'application_clock_skew'], true)) {
        throw new RuntimeException('Unexpected readiness result.');
    }

    $store = new api\modules\v1\services\LoginCodeStore($redis, $settings, $readiness);
    $consumerOutcome = 'unavailable';
    try {
        $result = $store->resolve(str_repeat('A', 64));
        if (($result['outcome'] ?? null) !== 'miss') {
            throw new RuntimeException('Unexpected ready consumer result.');
        }
        $consumerOutcome = 'miss';
    } catch (common\components\security\ServiceUnavailableHttpException) {
        if ($ready) {
            throw new RuntimeException('A ready gate blocked the consumer.');
        }
    }

    if (($ready && $consumerOutcome !== 'miss') || (!$ready && $consumerOutcome !== 'unavailable')) {
        throw new RuntimeException('Readiness gate did not control the consumer path.');
    }

    qrLoginCodeReadinessSkewMainEmit([
        'ok' => true,
        'service' => 'main-api',
        'readiness' => $reason,
        'consumer_outcome' => $consumerOutcome,
    ]);
} catch (Throwable) {
    qrLoginCodeReadinessSkewMainEmit([
        'ok' => false,
        'error' => 'probe_failed',
    ], 1);
}

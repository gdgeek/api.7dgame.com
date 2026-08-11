<?php

namespace api\modules\v1\services;

use common\components\security\ServiceUnavailableHttpException;
use Throwable;
use Yii;
use yii\base\Component;

/**
 * Login-code-specific readiness gate.
 *
 * The protocol is intentionally inactive in database/database mode. In every
 * Redis mode it verifies the shared Redis clock before the login-code path is
 * allowed to issue or resolve a bearer credential. redis-first additionally
 * verifies MySQL UTC time, because it may fall back to legacy DATETIME rows.
 */
final class LoginCodeReadiness extends Component
{
    private const MAX_CLOCK_SKEW_MILLISECONDS = 1000;

    public ?LoginCodeSettings $settings = null;
    /** @var mixed|null */
    public $redis = null;
    /** @var mixed|null */
    public $db = null;
    /** @var callable|null */
    public $clock = null;

    /**
     * @return array<string, mixed>
     */
    public function check(): array
    {
        $settings = $this->settings();
        if (!$settings->usesRedis()) {
            return [
                'status' => 'skipped',
                'required' => false,
            ];
        }

        try {
            // Use the app-clock midpoint around TIME so normal command
            // round-trip latency cannot masquerade as clock skew. Keep this
            // aligned with Yii3-A1's shared readiness-gate calculation.
            $applicationBeforeMilliseconds = $this->applicationTimeMilliseconds();
            $redisNowMilliseconds = $this->redisTimeMilliseconds();
            $applicationAfterMilliseconds = $this->applicationTimeMilliseconds();
            $applicationNowMilliseconds = intdiv(
                $applicationBeforeMilliseconds + $applicationAfterMilliseconds,
                2,
            );
            $applicationSkew = abs($applicationNowMilliseconds - $redisNowMilliseconds);
            if ($applicationSkew > self::MAX_CLOCK_SKEW_MILLISECONDS) {
                return $this->failure('application_clock_skew');
            }

            if ($settings->isRedisFirst()) {
                $mysqlNowMilliseconds = $this->mysqlUtcTimeMilliseconds();
                if (abs($mysqlNowMilliseconds - $redisNowMilliseconds) > self::MAX_CLOCK_SKEW_MILLISECONDS) {
                    return $this->failure('mysql_clock_skew');
                }
            }

            return [
                'status' => 'up',
                'required' => true,
                'protocol' => 'login-code-v1',
                'protocol_fingerprint' => $settings->protocolFingerprint(),
                'redis_database' => $this->redisDatabase(),
                'active_window_seconds' => LoginCodeSettings::ACTIVE_WINDOW_SECONDS,
                'record_retention_seconds' => LoginCodeSettings::RECORD_RETENTION_SECONDS,
                'issue_window_seconds' => LoginCodeSettings::ISSUE_WINDOW_SECONDS,
                'issue_limit' => $settings->issueLimit(),
                'limiter' => 'redis-zset-sliding-window',
                'clock_sync' => 'within_1s',
            ];
        } catch (Throwable $exception) {
            Yii::error('Login-code readiness dependency check failed.', 'login-code');
            return $this->failure('dependency_unavailable');
        }
    }

    public function assertReady(): void
    {
        $result = $this->check();
        if ($result['status'] === 'up' || $result['status'] === 'skipped') {
            return;
        }

        throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
    }

    private function settings(): LoginCodeSettings
    {
        if ($this->settings === null) {
            $this->settings = LoginCodeSettings::fromApplication();
        }

        return $this->settings;
    }

    private function redisTimeMilliseconds(): int
    {
        $time = $this->redis()->executeCommand('TIME', []);
        $seconds = is_array($time) ? $this->parseRedisInteger($time[0] ?? null) : null;
        $microseconds = is_array($time) ? $this->parseRedisInteger($time[1] ?? null) : null;
        if ($seconds === null || $seconds < 0 || $microseconds === null || $microseconds < 0 || $microseconds >= 1000000) {
            throw new \RuntimeException('Invalid Redis TIME response.');
        }

        return ($seconds * 1000) + intdiv($microseconds, 1000);
    }

    /** @param mixed $value */
    private function parseRedisInteger($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int)$value;
        }

        return null;
    }

    private function mysqlUtcTimeMilliseconds(): int
    {
        $microseconds = $this->database()->createCommand(
            "SELECT TIMESTAMPDIFF(MICROSECOND, '1970-01-01 00:00:00', UTC_TIMESTAMP(6))"
        )->queryScalar();
        $microseconds = $this->parseNonNegativeInteger($microseconds);
        if ($microseconds === null) {
            throw new \RuntimeException('Invalid MySQL UTC time response.');
        }

        return intdiv($microseconds, 1000);
    }

    /** @param mixed $value */
    private function parseNonNegativeInteger($value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || preg_match('/^(?:0|[1-9]\\d*)$/D', $value) !== 1) {
            return null;
        }

        $max = (string)PHP_INT_MAX;
        if (strlen($value) > strlen($max) || (strlen($value) === strlen($max) && strcmp($value, $max) > 0)) {
            return null;
        }

        return (int)$value;
    }

    private function applicationTimeMilliseconds(): int
    {
        if (is_callable($this->clock)) {
            return (int)call_user_func($this->clock);
        }

        return (int) round(microtime(true) * 1000);
    }

    /** @return mixed */
    private function redis()
    {
        if ($this->redis === null) {
            $this->redis = Yii::$app->get('redis');
        }

        return $this->redis;
    }

    /** @return mixed */
    private function database()
    {
        if ($this->db === null) {
            $this->db = Yii::$app->get('db');
        }

        return $this->db;
    }

    private function redisDatabase(): int
    {
        $redis = $this->redis();

        return isset($redis->database) ? (int)$redis->database : 0;
    }

    /** @return array{status: string, required: bool, error: string} */
    private function failure(string $reason): array
    {
        LoginCodeTelemetry::record('readiness_down', 'main-api-readiness');

        return [
            'status' => 'down',
            'required' => true,
            'error' => $reason,
        ];
    }
}

<?php

namespace common\components;

use Yii;
use yii\base\Component;

/**
 * HealthService 组件
 * 
 * 执行各项健康检查，聚合检查结果。
 */
class HealthService extends Component
{
    public const DATABASE_TIMEOUT = 5;
    public const REDIS_TIMEOUT = 3;
    private const LOGIN_CODE_REDIS_MEMORY_ALERT_PERCENT = 80;

    /**
     * 执行所有健康检查
     */
    public function check(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
        ];

        // 如果配置了 Redis，也检查 Redis
        if (Yii::$app->has('redis')) {
            $checks['redis'] = $this->checkRedis();
        }

        // Login-code readiness is intentionally absent from database/database
        // mode. It is a separate protocol gate, not a new dependency of the
        // legacy path.
        if ($this->requiresLoginCodeRedisCheck() && Yii::$app->has('loginCodeReadiness')) {
            try {
                $checks['login_code'] = Yii::$app->get('loginCodeReadiness')->check();
                if (($checks['login_code']['status'] ?? null) === 'up') {
                    $capacity = $this->checkLoginCodeRedisCapacity();
                    $checks['login_code'] = $capacity['status'] === 'up'
                        ? array_merge($checks['login_code'], $capacity)
                        : $capacity;
                }
            } catch (\Throwable $exception) {
                Yii::error('Login-code readiness health check failed.', 'login-code');
                $checks['login_code'] = [
                    'status' => 'down',
                    'required' => true,
                    'error' => 'dependency_unavailable',
                ];
            }
        }

        $isHealthy = true;
        foreach ($checks as $check) {
            if ($check['status'] === 'down') {
                $isHealthy = false;
                break;
            }
        }

        $result = [
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'checks' => $checks,
        ];

        // 读取构建时注入的 git commit hash
        $basePath = Yii::getAlias('@app');
        $candidates = [
            $basePath . '/GIT_COMMIT',
            dirname($basePath) . '/GIT_COMMIT',
        ];
        foreach ($candidates as $gitHashFile) {
            if (file_exists($gitHashFile)) {
                $result['gitCommit'] = trim(file_get_contents($gitHashFile));
                break;
            }
        }

        // 读取构建时注入的打包时间（北京时间）作为版本号
        $buildTimeCandidates = [
            $basePath . '/BUILD_TIME',
            dirname($basePath) . '/BUILD_TIME',
        ];
        foreach ($buildTimeCandidates as $buildTimeFile) {
            if (file_exists($buildTimeFile)) {
                $result['version'] = trim(file_get_contents($buildTimeFile));
                break;
            }
        }

        return $result;
    }

    /**
     * 检查数据库连接
     */
    protected function checkDatabase(): array
    {
        $startTime = microtime(true);

        try {
            $db = Yii::$app->db;

            // 确保数据库连接已打开
            if (!$db->isActive) {
                $db->open();
            }

            // 执行简单查询验证连接
            $db->createCommand('SELECT 1')->queryScalar();

            $responseTime = $this->calculateResponseTime($startTime);

            return [
                'status' => 'up',
                'responseTime' => $responseTime,
            ];
        } catch (\Exception $e) {
            $responseTime = $this->calculateResponseTime($startTime);

            return [
                'status' => 'down',
                'responseTime' => $responseTime,
                'error' => $this->formatErrorMessage($e),
            ];
        }
    }

    /**
     * 检查 Redis 连接
     */
    protected function checkRedis(): array
    {
        $startTime = microtime(true);

        try {
            $redis = Yii::$app->redis;

            $redis->connectionTimeout = self::REDIS_TIMEOUT;
            $redis->dataTimeout = self::REDIS_TIMEOUT;

            $response = $redis->executeCommand('PING');

            $responseTime = $this->calculateResponseTime($startTime);

            if ($response === 'PONG' || $response === true) {
                return [
                    'status' => 'up',
                    'responseTime' => $responseTime,
                ];
            }

            return [
                'status' => 'down',
                'responseTime' => $responseTime,
                'error' => 'Unexpected PING response',
            ];
        } catch (\Exception $e) {
            $responseTime = $this->calculateResponseTime($startTime);

            return [
                'status' => 'down',
                'responseTime' => $responseTime,
                'error' => $this->formatErrorMessage($e),
            ];
        }
    }

    /**
     * Docker polls /health continuously, so this is both a readiness gate and
     * the develop Redis memory/eviction alert required by the login-code
     * rollout. It runs only when a Redis login-code mode is selected.
     *
     * @return array<string, int|string|bool>
     */
    private function checkLoginCodeRedisCapacity(): array
    {
        try {
            $memory = $this->parseRedisInfo(
                Yii::$app->redis->executeCommand('INFO', ['memory'])
            );
            $stats = $this->parseRedisInfo(
                Yii::$app->redis->executeCommand('INFO', ['stats'])
            );
            $usedMemory = $this->parseNonNegativeRedisInteger($memory['used_memory'] ?? null);
            $maxMemory = $this->parseNonNegativeRedisInteger($memory['maxmemory'] ?? null);
            $evictedKeys = $this->parseNonNegativeRedisInteger($stats['evicted_keys'] ?? null);
            $policy = strtolower(trim((string)($memory['maxmemory_policy'] ?? '')));

            if ($usedMemory === null || $maxMemory === null || $maxMemory === 0 || $evictedKeys === null) {
                return $this->loginCodeCapacityFailure('redis_memory_configuration');
            }
            if ($policy !== 'noeviction') {
                return $this->loginCodeCapacityFailure('redis_eviction_policy');
            }
            if (($usedMemory / $maxMemory) * 100 >= self::LOGIN_CODE_REDIS_MEMORY_ALERT_PERCENT) {
                return $this->loginCodeCapacityFailure('redis_memory_threshold');
            }
            if ($evictedKeys !== 0) {
                return $this->loginCodeCapacityFailure('redis_evictions_detected');
            }

            return [
                'status' => 'up',
                'required' => true,
                'memory_alert_threshold_percent' => self::LOGIN_CODE_REDIS_MEMORY_ALERT_PERCENT,
                'memory_usage' => 'below_threshold',
                'maxmemory_policy' => 'noeviction',
                'eviction_alert' => 'configured_zero',
            ];
        } catch (\Throwable $exception) {
            Yii::error('Login-code Redis capacity health check failed.', 'login-code');
            return $this->loginCodeCapacityFailure('dependency_unavailable');
        }
    }

    /** @return array<string, string> */
    private function parseRedisInfo($value): array
    {
        $result = [];
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                if (is_string($key) && (is_scalar($item) || $item === null)) {
                    $result[strtolower($key)] = trim((string)$item);
                } elseif (is_array($item)) {
                    $result = array_merge($result, $this->parseRedisInfo($item));
                }
            }
            return $result;
        }

        foreach (preg_split('/\r?\n/', (string)$value) ?: [] as $line) {
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, ':')) {
                continue;
            }
            [$key, $item] = explode(':', $line, 2);
            $result[strtolower(trim($key))] = trim($item);
        }
        return $result;
    }

    private function parseNonNegativeRedisInteger($value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }
        if (!is_string($value) || preg_match('/^(?:0|[1-9]\d*)$/D', $value) !== 1) {
            return null;
        }
        $maximum = (string)PHP_INT_MAX;
        if (strlen($value) > strlen($maximum) || (strlen($value) === strlen($maximum) && strcmp($value, $maximum) > 0)) {
            return null;
        }
        return (int)$value;
    }

    /** @return array{status: string, required: bool, error: string} */
    private function loginCodeCapacityFailure(string $reason): array
    {
        return [
            'status' => 'down',
            'required' => true,
            'error' => $reason,
        ];
    }

    protected function calculateResponseTime(float $startTime): int
    {
        return (int) round((microtime(true) - $startTime) * 1000);
    }

    protected function formatErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        if (stripos($message, 'timeout') !== false || stripos($message, 'timed out') !== false) {
            return 'Connection timed out';
        }
        if (stripos($message, 'authentication') !== false || stripos($message, 'auth') !== false) {
            return 'Authentication failed';
        }
        if (stripos($message, 'connection refused') !== false) {
            return 'Service unavailable';
        }

        return strlen($message) > 100 ? substr($message, 0, 100) . '...' : $message;
    }

    private function requiresLoginCodeRedisCheck(): bool
    {
        $config = Yii::$app->params['loginCode'] ?? null;
        if (!is_array($config)) {
            return false;
        }

        $readMode = strtolower(trim((string)($config['readMode'] ?? 'database')));
        $writeMode = strtolower(trim((string)($config['writeMode'] ?? 'database')));

        return $readMode !== 'database' || $writeMode !== 'database';
    }
}

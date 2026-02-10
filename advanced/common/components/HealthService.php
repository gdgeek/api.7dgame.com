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
}

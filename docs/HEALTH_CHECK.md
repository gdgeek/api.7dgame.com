# Yii2 健康检查接口实现指南

本文档描述如何为 Yii2 RESTful API 项目添加健康检查接口，适用于 Docker/Kubernetes 健康探针场景。

## 功能特性

- `GET /health` 端点，无需认证
- 检查 MySQL 数据库连接状态
- 检查 Redis 连接状态
- 返回 JSON 格式响应
- 健康时返回 HTTP 200，不健康时返回 HTTP 503

## 实现步骤

### 1. 创建 HealthService 组件

创建文件 `components/HealthService.php`：

```php
<?php

namespace app\components;

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
            'redis' => $this->checkRedis(),
        ];

        $isHealthy = true;
        foreach ($checks as $check) {
            if ($check['status'] === 'down') {
                $isHealthy = false;
                break;
            }
        }

        return [
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'checks' => $checks,
        ];
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
```

### 2. 创建 HealthController 控制器

创建文件 `controllers/HealthController.php`：

```php
<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\components\HealthService;

/**
 * HealthController 处理健康检查请求
 */
class HealthController extends Controller
{
    private HealthService $_healthService;

    public function init(): void
    {
        parent::init();
        
        if (Yii::$app->has('healthService')) {
            $this->_healthService = Yii::$app->healthService;
        } else {
            $this->_healthService = new HealthService();
        }
    }

    /**
     * 禁用认证，允许公开访问
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    /**
     * GET /health
     */
    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Content-Type', 'application/json');
        
        $healthStatus = $this->_healthService->check();
        
        if ($healthStatus['status'] === 'unhealthy') {
            Yii::$app->response->statusCode = 503;
        } else {
            Yii::$app->response->statusCode = 200;
        }
        
        return $healthStatus;
    }
}
```

### 3. 配置路由和组件

在 `config/web.php` 中添加：

```php
return [
    // ...
    'components' => [
        'healthService' => [
            'class' => 'app\components\HealthService',
        ],
        // ... 其他组件
    ],
    // ...
];
```

在 `urlManager` 的 `rules` 中添加路由：

```php
'urlManager' => [
    'rules' => [
        'GET health' => 'health/index',
        // ... 其他路由
    ],
],
```

### 4. Docker 健康检查配置（可选）

在 `docker-compose.yml` 中添加：

```yaml
services:
  api:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
```

## 响应格式

### 健康状态（HTTP 200）

```json
{
    "status": "healthy",
    "timestamp": "2024-01-15T10:30:00+08:00",
    "checks": {
        "database": {
            "status": "up",
            "responseTime": 5
        },
        "redis": {
            "status": "up",
            "responseTime": 2
        }
    }
}
```

### 不健康状态（HTTP 503）

```json
{
    "status": "unhealthy",
    "timestamp": "2024-01-15T10:30:00+08:00",
    "checks": {
        "database": {
            "status": "down",
            "responseTime": 5001,
            "error": "Connection timed out"
        },
        "redis": {
            "status": "up",
            "responseTime": 2
        }
    }
}
```

## 注意事项

1. 如果项目没有使用 Redis，可以移除 `checkRedis()` 方法和相关调用
2. 可以根据需要添加其他依赖检查（如 Elasticsearch、外部 API 等）
3. 健康检查端点无需认证，适合 K8s liveness/readiness 探针使用

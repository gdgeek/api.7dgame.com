<?php

namespace tests\unit\components;

use common\components\HealthService;
use PHPUnit\Framework\TestCase;
use Yii;

final class HealthServiceCapacityTest extends TestCase
{
    private $originalRedis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalRedis = Yii::$app->get('redis');
    }

    protected function tearDown(): void
    {
        Yii::$app->set('redis', $this->originalRedis);
        parent::tearDown();
    }

    public function testHealthyCapacityReturnsOnlySafeAlertMetadata(): void
    {
        $result = $this->check(new CapacityRedis(10, 100, 'noeviction', 0));

        $this->assertSame([
            'status' => 'up',
            'required' => true,
            'memory_alert_threshold_percent' => 80,
            'memory_usage' => 'below_threshold',
            'maxmemory_policy' => 'noeviction',
            'eviction_alert' => 'configured_zero',
        ], $result);
    }

    public function testMemoryAtEightyPercentFailsClosed(): void
    {
        $this->assertSame(
            'redis_memory_threshold',
            $this->check(new CapacityRedis(80, 100, 'noeviction', 0))['error'],
        );
    }

    public function testEvictingPolicyFailsClosed(): void
    {
        $this->assertSame(
            'redis_eviction_policy',
            $this->check(new CapacityRedis(10, 100, 'allkeys-lru', 0))['error'],
        );
    }

    public function testAnyCumulativeEvictionFailsClosed(): void
    {
        $this->assertSame(
            'redis_evictions_detected',
            $this->check(new CapacityRedis(10, 100, 'noeviction', 1))['error'],
        );
    }

    public function testMissingMaxmemoryFailsClosed(): void
    {
        $this->assertSame(
            'redis_memory_configuration',
            $this->check(new CapacityRedis(10, 0, 'noeviction', 0))['error'],
        );
    }

    private function check(CapacityRedis $redis): array
    {
        Yii::$app->set('redis', $redis);
        $method = new \ReflectionMethod(HealthService::class, 'checkLoginCodeRedisCapacity');

        return $method->invoke(new HealthService());
    }
}

final class CapacityRedis
{
    public function __construct(
        private int $usedMemory,
        private int $maxMemory,
        private string $policy,
        private int $evictedKeys,
    ) {
    }

    public function executeCommand(string $command, array $arguments): array
    {
        if (strtoupper($command) !== 'INFO') {
            throw new \RuntimeException('Unexpected command.');
        }
        if (($arguments[0] ?? null) === 'memory') {
            return [
                'Memory' => [
                    'used_memory' => (string)$this->usedMemory,
                    'maxmemory' => (string)$this->maxMemory,
                    'maxmemory_policy' => $this->policy,
                ],
            ];
        }
        if (($arguments[0] ?? null) === 'stats') {
            return ['Stats' => ['evicted_keys' => (string)$this->evictedKeys]];
        }
        throw new \RuntimeException('Unexpected INFO section.');
    }
}

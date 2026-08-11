<?php

namespace tests\unit\components;

use common\components\security\AtomicRateLimiterStorageInterface;
use common\components\security\RateLimitBehavior;
use common\components\security\RateLimiter;
use common\components\security\RedisSlidingWindowRateLimiterStorage;
use common\components\security\ServiceUnavailableHttpException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use yii\base\Action;
use yii\base\Module;
use yii\web\Controller;
use yii\web\Response;

final class LoginCodeRateLimiterTest extends TestCase
{
    public function testRedisStorageUsesOneEvalAgainstOneExactKey(): void
    {
        $redis = new CapturingLoginCodeRateLimitRedis([1, 4, 1780000060, 60]);
        $storage = new RedisSlidingWindowRateLimiterStorage(['redis' => $redis]);

        $result = $storage->consume('test:login-code:v1:issue-rate:user-linked-issue:user_42', 5, 60);

        $this->assertSame([
            'allowed' => true,
            'remaining' => 4,
            'reset_at' => 1780000060,
            'retry_after' => 60,
        ], $result);
        $this->assertSame('EVAL', $redis->command());

        $arguments = $redis->arguments();
        $this->assertSame(1, $arguments[1]);
        $this->assertSame('test:login-code:v1:issue-rate:user-linked-issue:user_42', $arguments[2]);
        $this->assertSame(60000, $arguments[3]);
        $this->assertSame(5, $arguments[4]);
        $this->assertStringContainsString("redis.call('TIME')", (string)$arguments[0]);
        $this->assertStringContainsString("redis.call('ZREMRANGEBYSCORE'", (string)$arguments[0]);
        $this->assertStringContainsString("redis.call('ZADD'", (string)$arguments[0]);
        $this->assertStringContainsString('if allowed == 1 then', (string)$arguments[0]);
        $this->assertStringContainsString("redis.call('PEXPIRE'", (string)$arguments[0]);
    }

    public function testRedisStorageRejectsInvalidScriptResponse(): void
    {
        $storage = new RedisSlidingWindowRateLimiterStorage([
            'redis' => new CapturingLoginCodeRateLimitRedis(['not-enough']),
        ]);

        $this->expectException(RuntimeException::class);
        $storage->consume('test:login-code:v1:issue-rate:user-linked-issue:user_42', 5, 60);
    }

    public function testRedisStorageRejectsMalformedIntegerFieldsInsteadOfCoercingThem(): void
    {
        $storage = new RedisSlidingWindowRateLimiterStorage([
            'redis' => new CapturingLoginCodeRateLimitRedis(['1junk', '4', '1780000060', '60']),
        ]);

        $this->expectException(RuntimeException::class);
        $storage->consume('test:login-code:v1:issue-rate:user-linked-issue:user_42', 5, 60);
    }

    public function testAtomicBehaviorUsesSingleConsumeAndPreserves429Contract(): void
    {
        $storage = new FakeAtomicLoginCodeRateLimitStorage([
            'allowed' => false,
            'remaining' => 0,
            'reset_at' => 1780000060,
            'retry_after' => 43,
        ]);
        $limiter = $this->newIssueLimiter($storage);
        $response = new Response();
        \Yii::$app->set('response', $response);

        $behavior = new TestableAtomicLoginCodeRateLimitBehavior();
        $behavior->rateLimiter = $limiter;
        $behavior->testIdentifier = 'user_42';
        $behavior->defaultStrategy = 'user-linked-issue';
        $behavior->atomicConsume = true;

        $result = $behavior->beforeAction($this->action('user-linked'));

        $this->assertFalse($result);
        $this->assertSame(429, $response->statusCode);
        $this->assertSame('5', $response->headers->get('X-RateLimit-Limit'));
        $this->assertSame('0', $response->headers->get('X-RateLimit-Remaining'));
        $this->assertSame('1780000060', $response->headers->get('X-RateLimit-Reset'));
        $this->assertSame('43', $response->headers->get('Retry-After'));
        $this->assertSame([
            ['auth:login-code:v1:issue-rate:user-linked-issue:user_42', 5, 60],
        ], $storage->calls);
    }

    public function testAtomicBehaviorFailsClosedWhenStorageFails(): void
    {
        $storage = new FakeAtomicLoginCodeRateLimitStorage();
        $storage->failure = new RuntimeException('Redis unavailable');
        $limiter = $this->newIssueLimiter($storage);
        \Yii::$app->set('response', new Response());

        $behavior = new TestableAtomicLoginCodeRateLimitBehavior();
        $behavior->rateLimiter = $limiter;
        $behavior->testIdentifier = 'user_42';
        $behavior->defaultStrategy = 'user-linked-issue';
        $behavior->atomicConsume = true;

        try {
            $behavior->beforeAction($this->action('user-linked'));
            $this->fail('Expected a service-unavailable response.');
        } catch (ServiceUnavailableHttpException $exception) {
            $this->assertNull($exception->getPrevious());
            $this->assertSame('Rate limit service is temporarily unavailable.', $exception->getMessage());
        }
    }

    private function newIssueLimiter(FakeAtomicLoginCodeRateLimitStorage $storage): RateLimiter
    {
        $limiter = new RateLimiter();
        $limiter->keyPrefix = 'auth:login-code:v1:issue-rate:';
        $limiter->strategies = [
            'user-linked-issue' => ['limit' => 5, 'window' => 60],
        ];
        $limiter->init();
        $limiter->setStorage($storage);

        return $limiter;
    }

    private function action(string $id): Action
    {
        $module = $this->createMock(Module::class);
        $module->method('getUniqueId')->willReturn('test');
        $controller = $this->getMockBuilder(Controller::class)
            ->setConstructorArgs(['test-controller', $module])
            ->onlyMethods([])
            ->getMock();

        return new Action($id, $controller);
    }
}

final class CapturingLoginCodeRateLimitRedis
{
    private ?string $command = null;
    /** @var array<int, mixed> */
    private array $arguments = [];
    /** @var mixed */
    private $response;

    /** @param mixed $response */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function executeCommand(string $command, array $arguments)
    {
        $this->command = strtoupper($command);
        $this->arguments = $arguments;

        return $this->response;
    }

    public function command(): ?string
    {
        return $this->command;
    }

    /** @return array<int, mixed> */
    public function arguments(): array
    {
        return $this->arguments;
    }
}

final class FakeAtomicLoginCodeRateLimitStorage implements AtomicRateLimiterStorageInterface
{
    /** @var list<array{0: string, 1: int, 2: int}> */
    public array $calls = [];
    /** @var array{allowed: bool, remaining: int, reset_at: int, retry_after: int} */
    public array $result;
    public ?\Throwable $failure = null;

    /** @param array{allowed?: bool, remaining?: int, reset_at?: int, retry_after?: int} $result */
    public function __construct(array $result = [])
    {
        $this->result = array_merge([
            'allowed' => true,
            'remaining' => 4,
            'reset_at' => 1780000060,
            'retry_after' => 60,
        ], $result);
    }

    public function consume(string $key, int $limit, int $windowSeconds): array
    {
        $this->calls[] = [$key, $limit, $windowSeconds];
        if ($this->failure !== null) {
            throw $this->failure;
        }

        return $this->result;
    }

    public function add(string $key, float $timestamp): void
    {
    }

    public function count(string $key): int
    {
        return 0;
    }

    public function purgeExpired(string $key, float $threshold): void
    {
    }

    public function getOldest(string $key): ?float
    {
        return null;
    }

    public function clear(string $key): void
    {
    }
}

final class TestableAtomicLoginCodeRateLimitBehavior extends RateLimitBehavior
{
    public ?string $testIdentifier = null;

    protected function getIdentifier(): string
    {
        return $this->testIdentifier ?? parent::getIdentifier();
    }

    protected function resolveRateLimiter(): RateLimiter
    {
        if ($this->rateLimiter instanceof RateLimiter) {
            return $this->rateLimiter;
        }

        return parent::resolveRateLimiter();
    }
}

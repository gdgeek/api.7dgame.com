<?php

namespace tests\unit\services;

use api\modules\v1\services\LoginCodeReadiness;
use api\modules\v1\services\LoginCodeSettings;
use common\components\security\ServiceUnavailableHttpException;
use PHPUnit\Framework\TestCase;

final class LoginCodeReadinessTest extends TestCase
{
    public function testDatabaseOnlyModeSkipsRedisGateWithoutACommand(): void
    {
        $redis = new ReadinessRedis(1780000000000);
        $readiness = new LoginCodeReadiness([
            'settings' => new LoginCodeSettings(),
            'redis' => $redis,
            'clock' => static fn (): int => 1,
        ]);

        $this->assertSame([
            'status' => 'skipped',
            'required' => false,
        ], $readiness->check());
        $this->assertSame(0, $redis->commandCount());
    }

    public function testRedisModeReportsNonSensitiveProtocolReadiness(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'issueLimit' => 5,
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => new ReadinessRedis(1780000000000, 15),
            'clock' => static fn (): int => 1780000000500,
        ]);

        $result = $readiness->check();

        $this->assertSame('up', $result['status']);
        $this->assertSame('login-code-v1', $result['protocol']);
        $this->assertSame($settings->protocolFingerprint(), $result['protocol_fingerprint']);
        $this->assertSame(15, $result['redis_database']);
        $this->assertSame(60, $result['active_window_seconds']);
        $this->assertSame(300, $result['record_retention_seconds']);
        $this->assertSame('within_1s', $result['clock_sync']);
        $this->assertArrayNotHasKey('host', $result);
        $this->assertArrayNotHasKey('password', $result);
    }

    public function testRedisClockSkewFailsClosed(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => new ReadinessRedis(1780000000000),
            'clock' => static fn (): int => 1780000001001,
        ]);

        $this->assertSame('application_clock_skew', $readiness->check()['error']);
        $this->expectException(ServiceUnavailableHttpException::class);
        $readiness->assertReady();
    }

    public function testRedisClockSkewAtExactlyOneSecondIsAccepted(): void
    {
        $redisNowMilliseconds = 1780000000000;
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => new ReadinessRedis($redisNowMilliseconds),
            'clock' => static fn (): int => $redisNowMilliseconds - 1000,
        ]);

        $this->assertSame('up', $readiness->check()['status']);
        $readiness->assertReady();
    }

    public function testRedisModeUsesTheApplicationClockMidpointAroundRedisTime(): void
    {
        $redisNowMilliseconds = 1780000000000;
        $clockSamples = [
            $redisNowMilliseconds - 1001,
            $redisNowMilliseconds + 1001,
        ];
        $readiness = new LoginCodeReadiness([
            'settings' => new LoginCodeSettings([
                'readMode' => 'redis',
                'writeMode' => 'redis',
            ]),
            'redis' => new ReadinessRedis($redisNowMilliseconds),
            'clock' => static function () use (&$clockSamples): int {
                return array_shift($clockSamples);
            },
        ]);

        $this->assertSame('up', $readiness->check()['status']);
        $this->assertSame([], $clockSamples);
    }

    public function testReadinessDoesNotCacheAHealthyResultAcrossRequests(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
        ]);
        $redis = new ReadinessRedis(1780000000000);
        $applicationNow = 1780000000000;
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => $redis,
            'clock' => static function () use (&$applicationNow): int {
                return $applicationNow;
            },
        ]);

        $this->assertSame('up', $readiness->check()['status']);
        $applicationNow += 1001;

        $this->assertSame('application_clock_skew', $readiness->check()['error']);
        $this->assertSame(2, $redis->commandCount());
    }

    public function testRedisFirstAlsoRequiresMysqlUtcClockAgreement(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis-first',
            'writeMode' => 'dual',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => new ReadinessRedis(1780000000000),
            'db' => new ReadinessDatabase('1780000000500000'),
            'clock' => static fn (): int => 1780000000000,
        ]);

        $this->assertSame('up', $readiness->check()['status']);
    }

    public function testRedisFirstFailsClosedWhenMysqlClockDrifts(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis-first',
            'writeMode' => 'dual',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => new ReadinessRedis(1780000000000),
            'db' => new ReadinessDatabase('1780000001001000'),
            'clock' => static fn (): int => 1780000000000,
        ]);

        $this->assertSame('mysql_clock_skew', $readiness->check()['error']);
    }

    public function testMalformedRedisTimeResponseFailsClosed(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
        ]);
        $redis = new ReadinessRedis(1780000000000);
        $redis->timeOverride = ['1780000000.5', '1000000'];
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => $redis,
            'clock' => static fn (): int => 1780000000000,
        ]);

        $this->assertSame('dependency_unavailable', $readiness->check()['error']);
        $this->expectException(ServiceUnavailableHttpException::class);
        $readiness->assertReady();
    }

    public function testRedisFirstRejectsMalformedMysqlUtcTimeResponse(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis-first',
            'writeMode' => 'dual',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => new ReadinessRedis(1780000000000),
            'db' => new ReadinessDatabase('1780000000000000junk'),
            'clock' => static fn (): int => 1780000000000,
        ]);

        $this->assertSame('dependency_unavailable', $readiness->check()['error']);
    }
}

final class ReadinessRedis
{
    private int $commands = 0;
    /** @var mixed */
    public $timeOverride = null;

    public function __construct(private int $nowMilliseconds, public int $database = 0)
    {
    }

    /** @param array<int, mixed> $arguments */
    public function executeCommand(string $command, array $arguments): array
    {
        ++$this->commands;
        if (strtoupper($command) !== 'TIME') {
            throw new \RuntimeException('Unexpected Redis command.');
        }

        if ($this->timeOverride !== null) {
            return $this->timeOverride;
        }

        return [
            (string)intdiv($this->nowMilliseconds, 1000),
            (string)(($this->nowMilliseconds % 1000) * 1000),
        ];
    }

    public function commandCount(): int
    {
        return $this->commands;
    }
}

final class ReadinessDatabase
{
    public function __construct(private string $utcMicroseconds)
    {
    }

    public function createCommand(string $sql): ReadinessCommand
    {
        return new ReadinessCommand($this->utcMicroseconds);
    }
}

final class ReadinessCommand
{
    public function __construct(private string $result)
    {
    }

    public function queryScalar(): string
    {
        return $this->result;
    }
}

<?php

namespace tests\unit\services;

use api\modules\v1\services\LoginCodeSettings;
use api\modules\v1\services\LoginCodeStore;
use api\modules\v1\services\LoginCodeReadiness;
use common\components\security\ServiceUnavailableHttpException;
use PHPUnit\Framework\TestCase;

final class LoginCodeStoreTest extends TestCase
{
    private FakeLoginCodeRedis $redis;
    private LoginCodeStore $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = new FakeLoginCodeRedis(1780000000000);
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'prefix' => 'test:login-code:v1',
        ]);
        $this->store = new LoginCodeStore(
            $this->redis,
            $settings,
            new LoginCodeReadiness([
                'settings' => $settings,
                'redis' => $this->redis,
                'clock' => fn (): int => $this->redis->nowMilliseconds(),
            ])
        );
    }

    public function testIssueStoresOnlyDigestKeyAndImmutableJsonRecord(): void
    {
        $issued = $this->store->issue(123, ['device' => 'test']);

        $this->assertSame(64, strlen($issued['key']));
        $this->assertSame(1780000060, $issued['expires_at']);
        $this->assertSame(60, $issued['expires_in']);
        $this->assertCount(1, $this->redis->records());

        $key = array_key_first($this->redis->records());
        $payload = $this->redis->records()[$key]['payload'];
        $digest = hash('sha256', $issued['key']);
        $this->assertSame('test:login-code:v1:code:' . $digest, $key);
        $this->assertStringNotContainsString($issued['key'], $key);
        $this->assertStringNotContainsString($issued['key'], $payload);
        // The digest deliberately addresses the Redis key, but never belongs
        // in the value. The raw bearer code belongs in neither location.
        $this->assertStringNotContainsString($digest, $payload);

        $record = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(1, $record['v']);
        $this->assertSame(123, $record['user_id']);
        $this->assertSame(1780000000, $record['issued_at']);
        $this->assertSame(1780000060, $record['expires_at']);
        $this->assertSame('web-device-login', $record['purpose']);
        $this->assertSame('main-api', $record['issuer']);
        $this->assertSame(['device' => 'test'], $record['context']);
    }

    public function testIssuedRecordStartsWithThe300SecondPhysicalTtl(): void
    {
        $issued = $this->store->issue(123);
        $key = 'test:login-code:v1:code:' . hash('sha256', $issued['key']);

        $this->assertSame(300000, $this->redis->records()[$key]['pttl']);
        $this->assertContains('SET', $this->redis->commands());
    }

    public function testSameUserCanHoldMultipleIndependentCodes(): void
    {
        $first = $this->store->issue(123);
        $second = $this->store->issue(123);

        $this->assertNotSame($first['key'], $second['key']);
        $this->assertCount(2, $this->redis->records());
        $this->assertSame('hit', $this->store->resolve($first['key'])['outcome']);
        $this->assertSame('hit', $this->store->resolve($second['key'])['outcome']);
    }

    public function testIssuerRetriesAReservedWebPrefixInsteadOfIssuingAnAmbiguousCode(): void
    {
        $attempts = 0;
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'prefix' => 'test:login-code:v1',
        ]);
        $store = new LoginCodeStore(
            $this->redis,
            $settings,
            new LoginCodeReadiness([
                'settings' => $settings,
                'redis' => $this->redis,
                'clock' => fn (): int => $this->redis->nowMilliseconds(),
            ]),
            static function () use (&$attempts): string {
                ++$attempts;
                return $attempts === 1 ? 'web_' . str_repeat('x', 60) : str_repeat('a', 64);
            }
        );

        $issued = $store->issue(123);

        $this->assertSame(str_repeat('a', 64), $issued['key']);
        $this->assertSame(2, $attempts);
        $this->assertSame('hit', $store->resolve($issued['key'])['outcome']);
    }

    public function testPttlBoundaryExpiresWithoutDeletingRecord(): void
    {
        $issued = $this->store->issue(123);
        $key = 'test:login-code:v1:code:' . hash('sha256', $issued['key']);
        $this->redis->setPttl($key, 240000);

        $resolved = $this->store->resolve($issued['key']);

        $this->assertSame('expired', $resolved['outcome']);
        $this->assertArrayHasKey($key, $this->redis->records());
        $status = $this->store->status(123, $issued['key']);
        $this->assertFalse($status['active']);
        $this->assertSame('expired', $status['reason']);
    }

    public function testOneMillisecondAboveTheLogicalPttlBoundaryRemainsActive(): void
    {
        $issued = $this->store->issue(123);
        $key = 'test:login-code:v1:code:' . hash('sha256', $issued['key']);
        $this->redis->setPttl($key, 240001);

        $resolved = $this->store->resolve($issued['key']);
        $status = $this->store->status(123, $issued['key']);

        $this->assertSame('hit', $resolved['outcome']);
        $this->assertSame(1, $resolved['expires_in']);
        $this->assertTrue($status['active']);
        $this->assertSame('active', $status['reason']);
        $this->assertSame(1, $status['expires_in']);
    }

    public function testRedisTimeOneMillisecondBeforeExpiresAtRemainsActiveWhenPttlIsAboveBoundary(): void
    {
        $issued = $this->store->issue(123);
        $key = 'test:login-code:v1:code:' . hash('sha256', $issued['key']);
        $this->redis->setPttl($key, 240001);
        $this->redis->setNowMilliseconds(($issued['expires_at'] * 1000) - 1);

        $resolved = $this->store->resolve($issued['key']);

        // PTTL must not be the reason for this result: Redis TIME is exactly
        // one millisecond before expires_at while physical retention remains.
        $this->assertGreaterThan(240000, $this->redis->records()[$key]['pttl']);
        $this->assertSame('hit', $resolved['outcome']);
        $this->assertSame(1, $resolved['expires_in']);
    }

    public function testRedisTimeAtExpiresAtIsExpiredWhenPttlIsStillAboveBoundary(): void
    {
        $issued = $this->store->issue(123);
        $key = 'test:login-code:v1:code:' . hash('sha256', $issued['key']);
        $this->redis->setPttl($key, 240001);
        $this->redis->setNowMilliseconds($issued['expires_at'] * 1000);

        $resolved = $this->store->resolve($issued['key']);

        // This isolates the Redis TIME boundary from the independent PTTL
        // boundary: retention is still strictly more than 240 seconds.
        $this->assertGreaterThan(240000, $this->redis->records()[$key]['pttl']);
        $this->assertSame('expired', $resolved['outcome']);
    }

    public function testReadsDoNotRefreshTtlOrMutateRecord(): void
    {
        $issued = $this->store->issue(123);
        $key = 'test:login-code:v1:code:' . hash('sha256', $issued['key']);
        $this->redis->setPttl($key, 250001);
        $before = $this->redis->records()[$key];
        $commandCountBeforeReads = count($this->redis->commands());

        $this->assertSame('hit', $this->store->resolve($issued['key'])['outcome']);
        $this->assertSame('active', $this->store->status(123, $issued['key'])['reason']);

        $after = $this->redis->records()[$key];
        $this->assertSame($before, $after);
        $readCommands = array_slice($this->redis->commands(), $commandCountBeforeReads);
        $this->assertNotContains('EXPIRE', $readCommands);
        $this->assertNotContains('PEXPIRE', $readCommands);
        $this->assertNotContains('SET', $readCommands);
    }

    public function testDeleteExactPhysicallyDeletesOnlyTheRequestedCode(): void
    {
        $first = $this->store->issue(123);
        $second = $this->store->issue(456);
        $firstStorageKey = 'test:login-code:v1:code:' . hash('sha256', $first['key']);
        $secondStorageKey = 'test:login-code:v1:code:' . hash('sha256', $second['key']);
        $commandCountBeforeDelete = count($this->redis->commandCalls());

        $this->store->deleteExact($first['key']);

        $this->assertArrayNotHasKey($firstStorageKey, $this->redis->records());
        $this->assertArrayHasKey($secondStorageKey, $this->redis->records());
        $this->assertSame('miss', $this->store->resolve($first['key'])['outcome']);
        $this->assertSame('hit', $this->store->resolve($second['key'])['outcome']);

        $deleteCalls = array_values(array_filter(
            array_slice($this->redis->commandCalls(), $commandCountBeforeDelete),
            static fn (array $call): bool => $call['command'] === 'DEL'
        ));
        $this->assertSame([
            ['command' => 'DEL', 'arguments' => [$firstStorageKey]],
        ], $deleteCalls);
    }

    public function testExactKeyOperationsNeverScanRedis(): void
    {
        $first = $this->store->issue(123);
        $second = $this->store->issue(456);

        $this->assertSame('hit', $this->store->resolve($first['key'])['outcome']);
        $this->assertSame('active', $this->store->status(456, $second['key'])['reason']);
        $this->store->deleteExact($first['key']);

        $commands = $this->redis->commands();
        $this->assertNotContains('KEYS', $commands);
        $this->assertNotContains('SCAN', $commands);
        $this->assertNotContains('UNLINK', $commands);
        $this->assertContains('DEL', $commands);
    }

    public function testMalformedRecordFailsClosed(): void
    {
        $rawCode = str_repeat('x', 64);
        $key = 'test:login-code:v1:code:' . hash('sha256', $rawCode);
        $this->redis->put($key, '{"v":1}', 300000);

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->store->resolve($rawCode);
    }

    public function testLiveTtlWithoutAValueFailsClosedInsteadOfFallingBack(): void
    {
        $rawCode = str_repeat('m', 64);
        $key = 'test:login-code:v1:code:' . hash('sha256', $rawCode);
        $this->redis->putMissingPayload($key, 300000);

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->store->resolve($rawCode);
    }

    public function testFalseRedisGetResponseFailsClosedInsteadOfBeingAHealthyMiss(): void
    {
        $this->redis->payloadOverride = false;
        $this->redis->pttlOverride = -2;

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->store->resolve(str_repeat('q', 64));
    }

    public function testTtlGreaterThanTheProtocolMaximumFailsClosed(): void
    {
        $issued = $this->store->issue(123);
        $key = 'test:login-code:v1:code:' . hash('sha256', $issued['key']);
        $this->redis->setPttl($key, 300001);

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->store->resolve($issued['key']);
    }

    public function testMalformedPttlResponseFailsClosed(): void
    {
        $issued = $this->store->issue(123);
        $this->redis->pttlOverride = 'not-an-integer';

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->store->resolve($issued['key']);
    }

    public function testIssuerRejectsUnknownContextFields(): void
    {
        $this->expectException(\yii\web\ServerErrorHttpException::class);

        $this->store->issue(123, ['token' => 'must-not-be-persisted']);
    }

    public function testIssuerRejectsOverlongContextValues(): void
    {
        $this->expectException(\yii\web\ServerErrorHttpException::class);

        $this->store->issue(123, ['device' => str_repeat('d', 129)]);
    }

    public function testConsumerIgnoresUnknownOptionalFields(): void
    {
        $issued = $this->store->issue(123, ['device' => 'test']);
        $key = 'test:login-code:v1:code:' . hash('sha256', $issued['key']);
        $record = json_decode($this->redis->records()[$key]['payload'], true, 512, JSON_THROW_ON_ERROR);
        $record['future_optional_field'] = 'future';
        $record['context']['future_context_field'] = 'future';
        $this->redis->put($key, json_encode($record, JSON_THROW_ON_ERROR), 300000);

        $this->assertSame('hit', $this->store->resolve($issued['key'])['outcome']);
    }

    public function testOversizedRedisPayloadFailsClosed(): void
    {
        $rawCode = str_repeat('o', 64);
        $key = 'test:login-code:v1:code:' . hash('sha256', $rawCode);
        $this->redis->put($key, str_repeat('x', 2049), 300000);

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->store->resolve($rawCode);
    }

    public function testRedisFailureDoesNotExposeRawCodeOrDigestInExceptionOrYiiLogs(): void
    {
        $rawCode = str_repeat('s', 64);
        $digest = hash('sha256', $rawCode);
        // Let the actual readiness gate succeed first. The following GET then
        // exercises LoginCodeStore::redisCommand(), whose caught exception
        // contains both credential forms as a hostile driver could.
        $this->redis->failureOnCommand = 'GET';
        $this->redis->failureOnCommandException = new \RuntimeException(
            'Redis GET failed for raw=' . $rawCode . ' digest=' . $digest
        );
        $logger = \Yii::getLogger();
        $messageCountBeforeResolve = count($logger->messages);

        try {
            $this->store->resolve($rawCode);
            $this->fail('Expected a service-unavailable response.');
        } catch (ServiceUnavailableHttpException $exception) {
            $this->assertSame('Login code storage is temporarily unavailable.', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
            $this->assertStringNotContainsString($rawCode, $exception->getMessage());
            $this->assertStringNotContainsString($digest, $exception->getMessage());
        }

        $storageKey = 'test:login-code:v1:code:' . $digest;
        $getCalls = $this->redisCalls($this->redis, 'GET');
        $this->assertSame([['command' => 'GET', 'arguments' => [$storageKey]]], $getCalls);
        $this->assertStringNotContainsString($rawCode, $storageKey);

        $messages = array_slice($logger->messages, $messageCountBeforeResolve);
        $this->assertLoggerMessagesAreCredentialFree($messages, $rawCode, $digest);
        $this->assertContains(
            ['event' => 'redis_error', 'source' => 'main-api-refresh'],
            $this->telemetryEvents($messages)
        );
        $this->assertContains('Login-code Redis command failed.', $this->stringLogMessages($messages));
    }

    public function testDualLegacyFailureDoesNotExposeRawCodeOrDigestInExceptionOrYiiLogs(): void
    {
        $rawCode = str_repeat('t', 64);
        $digest = hash('sha256', $rawCode);
        $database = $this->dualWriteDatabase(false);
        $database->createCommand('INSERT INTO "user" (id) VALUES (123)')->execute();
        $database->createCommand(
            'INSERT INTO user_linked (id, user_id, key, created_at) VALUES (1, 123, :key, :createdAt)',
            [':key' => 'preexisting-legacy-digest', ':createdAt' => '2026-05-26 12:00:00']
        )->execute();
        // Model an underlying DB driver error that includes its bound
        // credential-equivalent value. The production Store must only emit
        // its fixed, redacted error/telemetry dimensions.
        $database->legacyWriteFailure = new \RuntimeException(
            'Legacy write failed for raw=' . $rawCode . ' digest=' . $digest
        );

        $redis = new FakeLoginCodeRedis(1780000000000);
        $store = $this->dualWriteStore($redis, $rawCode);
        $originalDatabase = \Yii::$app->get('db');
        $logger = \Yii::getLogger();
        $messageCountBeforeIssue = count($logger->messages);
        \Yii::$app->set('db', $database);

        try {
            try {
                $store->issue(123, ['source' => 'test']);
                $this->fail('Expected the injected legacy persistence failure.');
            } catch (ServiceUnavailableHttpException $exception) {
                $this->assertSame('Login code storage is temporarily unavailable.', $exception->getMessage());
                $this->assertNull($exception->getPrevious());
                $this->assertStringNotContainsString($rawCode, $exception->getMessage());
                $this->assertStringNotContainsString($digest, $exception->getMessage());
            }

            $storageKey = 'test:login-code:v1:code:' . $digest;
            $setCalls = $this->redisCalls($redis, 'SET');
            $this->assertCount(1, $setCalls);
            $this->assertSame($storageKey, $setCalls[0]['arguments'][0]);
            $this->assertStringNotContainsString($rawCode, $storageKey);
            $this->assertStringNotContainsString($rawCode, (string)$setCalls[0]['arguments'][1]);
            $this->assertStringNotContainsString($digest, (string)$setCalls[0]['arguments'][1]);
            $this->assertArrayNotHasKey($storageKey, $redis->records());

            $messages = array_slice($logger->messages, $messageCountBeforeIssue);
            $this->assertLoggerMessagesAreCredentialFree($messages, $rawCode, $digest);
            $this->assertContains(
                ['event' => 'db_write_failed', 'source' => 'main-api-issue'],
                $this->telemetryEvents($messages)
            );
            $this->assertContains('Login-code dual-write database persistence failed.', $this->stringLogMessages($messages));
        } finally {
            \Yii::$app->set('db', $originalDatabase);
            $database->close();
        }
    }

    public function testDualWriteCompensatesOnlyItsRedisRecordWhenLegacyWriteFails(): void
    {
        $rawCode = str_repeat('u', 64);
        $unrelatedRawCode = str_repeat('v', 64);
        $database = $this->dualWriteDatabase(false);
        $database->createCommand('INSERT INTO "user" (id) VALUES (123)')->execute();
        $database->createCommand(
            'INSERT INTO user_linked (id, user_id, key, created_at) VALUES (1, 123, :key, :createdAt)',
            [':key' => 'preexisting-legacy-digest', ':createdAt' => '2026-05-26 12:00:00']
        )->execute();

        $redis = new FakeLoginCodeRedis(1780000000000);
        $unrelatedStorageKey = 'test:login-code:v1:code:' . hash('sha256', $unrelatedRawCode);
        $redis->put($unrelatedStorageKey, '{"v":1}', 300000);
        $store = $this->dualWriteStore($redis, $rawCode);
        $originalDatabase = \Yii::$app->get('db');
        \Yii::$app->set('db', $database);

        try {
            try {
                $store->issue(123, ['source' => 'test']);
                $this->fail('Expected the legacy write to fail after Redis SET succeeded.');
            } catch (ServiceUnavailableHttpException $exception) {
                $this->assertSame('Login code storage is temporarily unavailable.', $exception->getMessage());
                $this->assertNull($exception->getPrevious());
                $this->assertStringNotContainsString($rawCode, $exception->getMessage());
            }

            $storageKey = 'test:login-code:v1:code:' . hash('sha256', $rawCode);
            $this->assertSame(1, $database->legacyWriteExecutions);
            $this->assertArrayNotHasKey($storageKey, $redis->records());
            $this->assertArrayHasKey($unrelatedStorageKey, $redis->records());
            $this->assertSame(
                'preexisting-legacy-digest',
                $database->createCommand('SELECT key FROM user_linked WHERE id = 1')->queryScalar()
            );
            $this->assertSame(
                [['command' => 'DEL', 'arguments' => [$storageKey]]],
                $this->redisCalls($redis, 'DEL')
            );
            $setCalls = $this->redisCalls($redis, 'SET');
            $this->assertCount(1, $setCalls);
            $this->assertSame($storageKey, $setCalls[0]['arguments'][0]);
            $this->assertSame(['PX', 300000, 'NX'], array_slice($setCalls[0]['arguments'], 2));
        } finally {
            \Yii::$app->set('db', $originalDatabase);
            $database->close();
        }
    }

    public function testDualWriteCommitFailureCompensatesTheExactRedisRecord(): void
    {
        $rawCode = str_repeat('w', 64);
        $unrelatedRawCode = str_repeat('x', 64);
        $database = $this->dualWriteDatabase(true);
        $database->createCommand('INSERT INTO "user" (id) VALUES (123)')->execute();
        $database->createCommand(
            'INSERT INTO user_linked (id, user_id, key, created_at) VALUES (1, 123, :key, :createdAt)',
            [':key' => 'preexisting-legacy-digest', ':createdAt' => '2026-05-26 12:00:00']
        )->execute();

        $redis = new FakeLoginCodeRedis(1780000000000);
        $unrelatedStorageKey = 'test:login-code:v1:code:' . hash('sha256', $unrelatedRawCode);
        $redis->put($unrelatedStorageKey, '{"v":1}', 300000);
        $store = $this->dualWriteStore($redis, $rawCode);
        $originalDatabase = \Yii::$app->get('db');
        \Yii::$app->set('db', $database);

        try {
            try {
                $store->issue(123, ['source' => 'test']);
                $this->fail('Expected the test transaction commit to fail.');
            } catch (ServiceUnavailableHttpException $exception) {
                $this->assertSame('Login code storage is temporarily unavailable.', $exception->getMessage());
                $this->assertNull($exception->getPrevious());
                $this->assertStringNotContainsString($rawCode, $exception->getMessage());
            }

            $storageKey = 'test:login-code:v1:code:' . hash('sha256', $rawCode);
            $this->assertSame(1, $database->legacyWriteExecutions);
            $this->assertSame(1, $database->commitAttempts);
            $this->assertSame(1, $database->rollbackAttempts);
            $this->assertArrayNotHasKey($storageKey, $redis->records());
            $this->assertArrayHasKey($unrelatedStorageKey, $redis->records());
            $this->assertSame(
                'preexisting-legacy-digest',
                $database->createCommand('SELECT key FROM user_linked WHERE id = 1')->queryScalar()
            );
            $this->assertSame(
                [['command' => 'DEL', 'arguments' => [$storageKey]]],
                $this->redisCalls($redis, 'DEL')
            );
        } finally {
            \Yii::$app->set('db', $originalDatabase);
            $database->close();
        }
    }

    public function testDualWriteCompensationFailureStillFailsClosedWithoutLeakingTheCode(): void
    {
        $rawCode = str_repeat('y', 64);
        $database = $this->dualWriteDatabase(false);
        $database->createCommand('INSERT INTO "user" (id) VALUES (123)')->execute();
        $database->createCommand(
            'INSERT INTO user_linked (id, user_id, key, created_at) VALUES (1, 123, :key, :createdAt)',
            [':key' => 'preexisting-legacy-digest', ':createdAt' => '2026-05-26 12:00:00']
        )->execute();

        $redis = new FakeLoginCodeRedis(1780000000000);
        $redis->failureOnCommand = 'DEL';
        $redis->failureOnCommandException = new \RuntimeException('Test-only DEL failure for ' . $rawCode);
        $store = $this->dualWriteStore($redis, $rawCode);
        $originalDatabase = \Yii::$app->get('db');
        $logger = \Yii::getLogger();
        $messageCountBeforeIssue = count($logger->messages);
        \Yii::$app->set('db', $database);

        try {
            try {
                $store->issue(123, ['source' => 'test']);
                $this->fail('Expected the legacy write to fail and compensation to be attempted.');
            } catch (ServiceUnavailableHttpException $exception) {
                $this->assertSame('Login code storage is temporarily unavailable.', $exception->getMessage());
                $this->assertNull($exception->getPrevious());
                $this->assertStringNotContainsString($rawCode, $exception->getMessage());
            }

            $storageKey = 'test:login-code:v1:code:' . hash('sha256', $rawCode);
            $this->assertArrayHasKey($storageKey, $redis->records());
            $this->assertSame(
                [['command' => 'DEL', 'arguments' => [$storageKey]]],
                $this->redisCalls($redis, 'DEL')
            );

            $messages = array_slice($logger->messages, $messageCountBeforeIssue);
            $events = [];
            foreach ($messages as $message) {
                $content = is_string($message[0])
                    ? $message[0]
                    : json_encode($message[0], JSON_THROW_ON_ERROR);
                $this->assertStringNotContainsString($rawCode, $content);
                if (is_array($message[0])) {
                    $events[] = $message[0];
                }
            }
            $this->assertContains(['event' => 'compensation_failed', 'source' => 'main-api-issue'], $events);
            $this->assertContains(['event' => 'db_write_failed', 'source' => 'main-api-issue'], $events);
        } finally {
            \Yii::$app->set('db', $originalDatabase);
            $database->close();
        }
    }

    private function dualWriteStore(FakeLoginCodeRedis $redis, string $rawCode): LoginCodeStore
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'database',
            'writeMode' => 'dual',
            'prefix' => 'test:login-code:v1',
        ]);

        return new LoginCodeStore(
            $redis,
            $settings,
            new LoginCodeReadiness([
                'settings' => $settings,
                'redis' => $redis,
                'clock' => static fn (): int => $redis->nowMilliseconds(),
            ]),
            static fn (): string => $rawCode,
        );
    }

    private function dualWriteDatabase(bool $failCommit): DualWriteSqliteDatabase
    {
        $database = new DualWriteSqliteDatabase([
            'dsn' => 'sqlite::memory:',
            'commandClass' => DualWriteSqliteCommand::class,
            'normalizeLegacyTimestampExpression' => $failCommit,
            'failCommit' => $failCommit,
        ]);
        $database->open();
        $database->createCommand('CREATE TABLE "user" (id INTEGER PRIMARY KEY)')->execute();
        $database->createCommand(
            'CREATE TABLE user_linked ('
            . 'id INTEGER PRIMARY KEY, '
            . 'user_id INTEGER NOT NULL, '
            . 'key TEXT NOT NULL, '
            . 'created_at TEXT NOT NULL'
            . ')'
        )->execute();

        return $database;
    }

    /** @return list<array{command: string, arguments: array<int, mixed>}> */
    private function redisCalls(FakeLoginCodeRedis $redis, string $command): array
    {
        return array_values(array_filter(
            $redis->commandCalls(),
            static fn (array $call): bool => $call['command'] === $command
        ));
    }

    /** @param list<array<int, mixed>> $messages */
    private function assertLoggerMessagesAreCredentialFree(array $messages, string $rawCode, string $digest): void
    {
        $this->assertNotEmpty($messages);

        foreach ($messages as $message) {
            // Yii logger entries include the emitted payload, level, category,
            // timestamp and trace metadata. Serialize the whole entry instead
            // of inspecting only message[0], so a future logger/target change
            // cannot move a credential into another emitted field unnoticed.
            $serialized = serialize($message);
            $this->assertStringNotContainsString($rawCode, $serialized);
            $this->assertStringNotContainsString($digest, $serialized);
        }
    }

    /**
     * @param list<array<int, mixed>> $messages
     * @return list<array{event: string, source: string}>
     */
    private function telemetryEvents(array $messages): array
    {
        $events = [];
        foreach ($messages as $message) {
            if (is_array($message[0] ?? null)) {
                $events[] = $message[0];
            }
        }

        return $events;
    }

    /**
     * @param list<array<int, mixed>> $messages
     * @return list<string>
     */
    private function stringLogMessages(array $messages): array
    {
        $entries = [];
        foreach ($messages as $message) {
            if (is_string($message[0] ?? null)) {
                $entries[] = $message[0];
            }
        }

        return $entries;
    }

    public function testRedisModeBuildsItsReadinessGateAndFailsClosedOnClockSkew(): void
    {
        $settings = new LoginCodeSettings([
            'readMode' => 'redis',
            'writeMode' => 'redis',
            'prefix' => 'test:login-code:v1',
        ]);
        $store = new LoginCodeStore(new FakeLoginCodeRedis(1), $settings);

        try {
            $store->resolve(str_repeat('c', 64));
            $this->fail('Expected the readiness gate to reject clock skew.');
        } catch (ServiceUnavailableHttpException $exception) {
            $this->assertSame('Login code storage is temporarily unavailable.', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function testRedisFirstFallbackReadsCurrentKeyAndTimestampInOneSnapshot(): void
    {
        $rawCode = str_repeat('f', 64);
        $settings = new LoginCodeSettings([
            'readMode' => 'redis-first',
            'writeMode' => 'dual',
            'prefix' => 'test:login-code:v1',
        ]);
        $redis = new FakeLoginCodeRedis(1780000000000);
        $database = new AtomicLegacyFallbackDatabase([
            'user_id' => 123,
            'created_at_epoch' => '1779999999',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => $redis,
            'db' => $database,
            'clock' => static fn (): int => 1780000000000,
        ]);
        $store = new LoginCodeStore($redis, $settings, $readiness);
        $originalDatabase = \Yii::$app->get('db');
        \Yii::$app->set('db', $database);

        try {
            $this->assertSame('hit', $store->resolve($rawCode)['outcome']);
        } finally {
            \Yii::$app->set('db', $originalDatabase);
        }

        $fallbackSql = $database->queryOneSql();
        $this->assertStringContainsString('[[linked]].[[key]] IN', $fallbackSql);
        $this->assertStringContainsString('NOT EXISTS', $fallbackSql);
        $this->assertStringContainsString("TIMESTAMPDIFF(SECOND, '1970-01-01 00:00:00', CONVERT_TZ([[linked]].[[created_at]], '+08:00', '+00:00'))", $fallbackSql);
        $this->assertStringNotContainsString('WHERE [[id]] = :id', $fallbackSql);
        $this->assertSame([[false, false]], $database->credentialQueryFlags());
        $this->assertTrue($database->enableLogging);
        $this->assertTrue($database->enableProfiling);
    }

    public function testRedisFirstStatusFallbackUsesOneLatestUserSnapshot(): void
    {
        $rawCode = str_repeat('g', 64);
        $settings = new LoginCodeSettings([
            'readMode' => 'redis-first',
            'writeMode' => 'dual',
            'prefix' => 'test:login-code:v1',
        ]);
        $redis = new FakeLoginCodeRedis(1780000000000);
        $database = new AtomicLegacyFallbackDatabase([
            'user_id' => 123,
            'key' => hash('sha256', $rawCode),
            'created_at_epoch' => '1779999999',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => $redis,
            'db' => $database,
            'clock' => static fn (): int => 1780000000000,
        ]);
        $store = new LoginCodeStore($redis, $settings, $readiness);
        $originalDatabase = \Yii::$app->get('db');
        \Yii::$app->set('db', $database);

        try {
            $this->assertSame('active', $store->status(123, $rawCode)['reason']);
        } finally {
            \Yii::$app->set('db', $originalDatabase);
        }

        $fallbackSql = $database->queryOneSql();
        $this->assertStringContainsString('[[linked]].[[user_id]] = :loginCodeUserId', $fallbackSql);
        $this->assertStringContainsString('ORDER BY [[linked]].[[id]] DESC LIMIT 1', $fallbackSql);
        $this->assertStringContainsString("TIMESTAMPDIFF(SECOND, '1970-01-01 00:00:00', CONVERT_TZ([[linked]].[[created_at]], '+08:00', '+00:00'))", $fallbackSql);
        $this->assertStringNotContainsString('WHERE [[id]] = :id', $fallbackSql);
        $this->assertSame([[true, true]], $database->credentialQueryFlags());
    }

    public function testRedisFirstFallbackRejectsFutureLegacyTimestamp(): void
    {
        $rawCode = str_repeat('h', 64);
        $settings = new LoginCodeSettings([
            'readMode' => 'redis-first',
            'writeMode' => 'dual',
            'prefix' => 'test:login-code:v1',
        ]);
        $redis = new FakeLoginCodeRedis(1780000000000);
        $database = new AtomicLegacyFallbackDatabase([
            'user_id' => 123,
            'created_at_epoch' => '1780000001',
        ]);
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => $redis,
            'db' => $database,
            'clock' => static fn (): int => 1780000000000,
        ]);
        $store = new LoginCodeStore($redis, $settings, $readiness);
        $originalDatabase = \Yii::$app->get('db');
        \Yii::$app->set('db', $database);

        try {
            $this->assertSame('expired', $store->resolve($rawCode)['outcome']);
        } finally {
            \Yii::$app->set('db', $originalDatabase);
        }
    }

    public function testDatabaseModeCredentialLookupDisablesSqlLoggingAndProfiling(): void
    {
        $rawCode = str_repeat('i', 64);
        $settings = new LoginCodeSettings([
            'readMode' => 'database',
            'writeMode' => 'database',
            'prefix' => 'test:login-code:v1',
        ]);
        $database = new AtomicLegacyFallbackDatabase([
            'user_id' => 123,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $store = new LoginCodeStore(null, $settings);
        $originalDatabase = \Yii::$app->get('db');
        \Yii::$app->set('db', $database);

        try {
            $this->assertSame('hit', $store->resolve($rawCode)['outcome']);
        } finally {
            \Yii::$app->set('db', $originalDatabase);
        }

        $this->assertSame([[false, false]], $database->credentialQueryFlags());
        $this->assertTrue($database->enableLogging);
        $this->assertTrue($database->enableProfiling);
    }

    public function testDatabaseModeCannotRedeemAStoredDigestAsIfItWereTheRawCode(): void
    {
        $rawCode = str_repeat('j', 64);
        $digest = hash('sha256', $rawCode);
        $settings = new LoginCodeSettings([
            'readMode' => 'database',
            'writeMode' => 'database',
        ]);
        $database = new AtomicLegacyFallbackDatabase([
            'user_id' => 123,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $database->returnRowOnlyForBoundValue = $digest;
        $store = new LoginCodeStore(null, $settings);
        $originalDatabase = \Yii::$app->get('db');
        \Yii::$app->set('db', $database);

        try {
            $this->assertSame('miss', $store->resolve($digest)['outcome']);
        } finally {
            \Yii::$app->set('db', $originalDatabase);
        }
    }

    public function testRedisFirstCannotFallbackFromDigestToTheStoredRawCredentialDigest(): void
    {
        $rawCode = str_repeat('k', 64);
        $digest = hash('sha256', $rawCode);
        $settings = new LoginCodeSettings([
            'readMode' => 'redis-first',
            'writeMode' => 'dual',
        ]);
        $redis = new FakeLoginCodeRedis(1780000000000);
        $database = new AtomicLegacyFallbackDatabase([
            'user_id' => 123,
            'created_at_epoch' => '1779999999',
        ]);
        $database->returnRowOnlyForBoundValue = $digest;
        $readiness = new LoginCodeReadiness([
            'settings' => $settings,
            'redis' => $redis,
            'db' => $database,
            'clock' => static fn (): int => 1780000000000,
        ]);
        $store = new LoginCodeStore($redis, $settings, $readiness);
        $originalDatabase = \Yii::$app->get('db');
        \Yii::$app->set('db', $database);

        try {
            $this->assertSame('miss', $store->resolve($digest)['outcome']);
        } finally {
            \Yii::$app->set('db', $originalDatabase);
        }
    }

    public function testDualShadowTimestampUsesAnExplicitFixedTimezoneExpression(): void
    {
        $method = new \ReflectionMethod(LoginCodeStore::class, 'legacyCreatedAtExpression');
        $expression = $method->invoke($this->store, 1780000000);

        $this->assertInstanceOf(\yii\db\Expression::class, $expression);
        $this->assertSame(
            "CONVERT_TZ(DATE_ADD('1970-01-01 00:00:00', INTERVAL :loginCodeIssuedAt SECOND), '+00:00', '+08:00')",
            $expression->expression
        );
        $this->assertSame([':loginCodeIssuedAt' => 1780000000], $expression->params);
        $this->assertStringNotContainsString('FROM_UNIXTIME', $expression->expression);
    }
}

/**
 * Minimal deterministic Redis adapter for LoginCodeStore unit tests.
 * It intentionally supports only exact test-owned keys and never scans.
 */
final class FakeLoginCodeRedis
{
    /** @var array<string, array{payload: string|null, pttl: int}> */
    private array $records = [];
    /** @var list<string> */
    private array $commands = [];
    /** @var list<array{command: string, arguments: array<int, mixed>}> */
    private array $commandCalls = [];
    public ?\Throwable $failure = null;
    public ?string $failureOnCommand = null;
    public ?\Throwable $failureOnCommandException = null;
    /** @var mixed */
    public $pttlOverride = null;
    /** @var mixed */
    public $payloadOverride = null;

    public function __construct(private int $nowMilliseconds)
    {
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function executeCommand(string $command, array $arguments)
    {
        $command = strtoupper($command);
        $this->commands[] = $command;
        $this->commandCalls[] = ['command' => $command, 'arguments' => $arguments];

        if ($this->failure !== null) {
            throw $this->failure;
        }

        if ($this->failureOnCommand === $command) {
            throw $this->failureOnCommandException ?? new \RuntimeException('Test Redis command failure.');
        }

        return match ($command) {
            'TIME' => [
                (string)intdiv($this->nowMilliseconds, 1000),
                (string)(($this->nowMilliseconds % 1000) * 1000),
            ],
            'SET' => $this->set($arguments),
            'GET' => $this->payloadOverride ?? ($this->records[$arguments[0]]['payload'] ?? null),
            'PTTL' => $this->pttlOverride ?? ($this->records[$arguments[0]]['pttl'] ?? -2),
            'DEL' => $this->delete((string)$arguments[0]),
            default => throw new \RuntimeException('Unexpected Redis command: ' . $command),
        };
    }

    /** @return array<string, array{payload: string|null, pttl: int}> */
    public function records(): array
    {
        return $this->records;
    }

    /** @return list<string> */
    public function commands(): array
    {
        return $this->commands;
    }

    /** @return list<array{command: string, arguments: array<int, mixed>}> */
    public function commandCalls(): array
    {
        return $this->commandCalls;
    }

    public function setNowMilliseconds(int $nowMilliseconds): void
    {
        $this->nowMilliseconds = $nowMilliseconds;
    }

    public function nowMilliseconds(): int
    {
        return $this->nowMilliseconds;
    }

    public function setPttl(string $key, int $pttl): void
    {
        $this->records[$key]['pttl'] = $pttl;
    }

    public function put(string $key, string $payload, int $pttl): void
    {
        $this->records[$key] = ['payload' => $payload, 'pttl' => $pttl];
    }

    public function putMissingPayload(string $key, int $pttl): void
    {
        $this->records[$key] = ['payload' => null, 'pttl' => $pttl];
    }

    /** @param array<int, mixed> $arguments */
    private function set(array $arguments): ?string
    {
        $key = (string)$arguments[0];
        if (isset($this->records[$key])) {
            return null;
        }

        $this->records[$key] = [
            'payload' => (string)$arguments[1],
            'pttl' => (int)$arguments[3],
        ];

        return 'OK';
    }

    private function delete(string $key): int
    {
        if (!isset($this->records[$key])) {
            return 0;
        }

        unset($this->records[$key]);
        return 1;
    }
}

final class AtomicLegacyFallbackDatabase
{
    public bool $enableLogging = true;
    public bool $enableProfiling = true;
    public ?string $returnRowOnlyForBoundValue = null;
    /** @var list<string> */
    private array $sql = [];
    /** @var list<array{0: bool, 1: bool}> */
    private array $credentialQueryFlags = [];

    /** @param array<string, mixed> $row */
    public function __construct(private array $row)
    {
    }

    /** @param array<string, mixed> $params */
    public function createCommand(string $sql, array $params = []): AtomicLegacyFallbackCommand
    {
        $this->sql[] = $sql;
        return new AtomicLegacyFallbackCommand($sql, $params, $this->row, $this);
    }

    public function queryOneSql(): string
    {
        foreach ($this->sql as $sql) {
            if (str_contains($sql, 'TIMESTAMPDIFF(SECOND')) {
                return $sql;
            }
        }

        throw new \RuntimeException('The legacy fallback query was not executed.');
    }

    public function recordCredentialQueryFlags(): void
    {
        $this->credentialQueryFlags[] = [$this->enableLogging, $this->enableProfiling];
    }

    /** @return list<array{0: bool, 1: bool}> */
    public function credentialQueryFlags(): array
    {
        return $this->credentialQueryFlags;
    }

    /** @param array<string, mixed> $params */
    public function acceptsBoundParameters(array $params): bool
    {
        return $this->returnRowOnlyForBoundValue === null
            || in_array($this->returnRowOnlyForBoundValue, $params, true);
    }
}

final class AtomicLegacyFallbackCommand
{
    /** @param array<string, mixed> $row */
    public function __construct(
        private string $sql,
        /** @var array<string, mixed> */
        private array $params,
        private array $row,
        private AtomicLegacyFallbackDatabase $database,
    )
    {
    }

    /** @return mixed */
    public function queryScalar()
    {
        if (str_contains($this->sql, 'UTC_TIMESTAMP(6)')) {
            return '1780000000000000';
        }

        throw new \RuntimeException('Unexpected scalar query.');
    }

    /** @return array<string, mixed>|false */
    public function queryOne(): array|false
    {
        $this->database->recordCredentialQueryFlags();
        return $this->database->acceptsBoundParameters($this->params) ? $this->row : false;
    }
}

/**
 * In-memory test database used only to exercise the real Yii2 dual-write
 * transaction path. SQLite does not accept MySQL's FOR UPDATE syntax, so the
 * narrow adapter removes that lock suffix after LoginCodeStore has constructed
 * its production SQL.
 */
final class DualWriteSqliteDatabase extends \yii\db\Connection
{
    public bool $normalizeLegacyTimestampExpression = false;
    public bool $failCommit = false;
    public ?\Throwable $legacyWriteFailure = null;
    public int $legacyWriteExecutions = 0;
    public int $commitAttempts = 0;
    public int $rollbackAttempts = 0;

    public function createCommand($sql = null, $params = [])
    {
        if (is_string($sql)) {
            $sql = preg_replace('/\s+FOR\s+UPDATE\s*$/i', '', $sql) ?? $sql;
        }

        return parent::createCommand($sql, $params);
    }

    public function beginTransaction($isolationLevel = null)
    {
        $transaction = parent::beginTransaction($isolationLevel);
        if (!$this->failCommit) {
            return $transaction;
        }

        return new DualWriteCommitFailureTransaction($transaction, $this);
    }
}

/**
 * Lets the commit-failure test execute the actual update before an injected
 * commit error. The production expression remains unchanged; only SQLite's
 * test command substitutes its unsupported MySQL-only timestamp expression.
 */
final class DualWriteSqliteCommand extends \yii\db\sqlite\Command
{
    public function execute()
    {
        if ($this->db instanceof DualWriteSqliteDatabase) {
            $database = $this->db;
            $sql = $this->getSql();
            if (is_string($sql) && str_contains($sql, 'CONVERT_TZ(DATE_ADD(')) {
                ++$database->legacyWriteExecutions;
                if ($database->legacyWriteFailure !== null) {
                    throw $database->legacyWriteFailure;
                }
                if ($database->normalizeLegacyTimestampExpression) {
                    $rewritten = preg_replace(
                        "~CONVERT_TZ\\(DATE_ADD\\('1970-01-01 00:00:00', INTERVAL\\s+:loginCodeIssuedAt\\s+SECOND\\),\\s*'\\+00:00',\\s*'\\+08:00'\\)~",
                        'CURRENT_TIMESTAMP',
                        $sql
                    );
                    if (!is_string($rewritten) || $rewritten === $sql) {
                        throw new \RuntimeException('Unable to normalize the test-only legacy timestamp expression.');
                    }

                    $this->setSql($rewritten);
                }
            }
        }

        return parent::execute();
    }
}

/**
 * A commit seam for the in-memory database. The inner transaction is still a
 * real SQLite transaction; throwing before its commit leaves it active so the
 * production catch block performs the normal rollback and Redis compensation.
 */
final class DualWriteCommitFailureTransaction
{
    public bool $isActive;

    public function __construct(
        private \yii\db\Transaction $transaction,
        private DualWriteSqliteDatabase $database,
    ) {
        $this->isActive = $transaction->isActive;
    }

    public function commit(): void
    {
        ++$this->database->commitAttempts;
        throw new \RuntimeException('Test-only legacy transaction commit failure.');
    }

    public function rollBack(): void
    {
        ++$this->database->rollbackAttempts;
        if ($this->transaction->isActive) {
            $this->transaction->rollBack();
        }

        $this->isActive = false;
    }
}

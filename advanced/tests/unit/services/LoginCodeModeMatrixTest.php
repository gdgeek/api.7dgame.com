<?php

namespace tests\unit\services;

use api\modules\v1\services\LoginCodeReadiness;
use api\modules\v1\services\LoginCodeSettings;
use api\modules\v1\services\LoginCodeStore;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\db\Connection;

/**
 * Behavioural mode matrix for the main API login-code boundary.
 *
 * The database is an in-memory SQLite fixture owned by each test.  The Redis
 * adapter is intentionally limited to the exact protocol commands so the
 * assertions can prove which mode does (and does not) use Code_Record state.
 */
final class LoginCodeModeMatrixTest extends TestCase
{
    private mixed $originalDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDatabase = Yii::$app->get('db', false);
    }

    protected function tearDown(): void
    {
        $database = Yii::$app->get('db', false);
        if ($database instanceof Connection && $database !== $this->originalDatabase) {
            $database->close();
        }
        Yii::$app->set('db', $this->originalDatabase);

        parent::tearDown();
    }

    public function testDatabaseBaselineKeepsLatestOneCodeAndDoesNotTouchRedisProtocol(): void
    {
        $database = $this->legacyDatabase();
        $olderCode = str_repeat('a', 64);
        $latestCode = str_repeat('b', 64);
        $createdAt = date('Y-m-d H:i:s');
        $this->insertLegacyRow($database, 1, 42, $olderCode, $createdAt);
        $this->insertLegacyRow($database, 2, 42, $latestCode, $createdAt);

        $nowMilliseconds = (int) round(microtime(true) * 1000);
        $redis = new ModeMatrixLoginCodeRedis($nowMilliseconds);
        $settings = new LoginCodeSettings([
            'readMode' => LoginCodeSettings::READ_DATABASE,
            'writeMode' => LoginCodeSettings::WRITE_DATABASE,
            'prefix' => 'test:mode-matrix:v1',
        ]);
        $store = new LoginCodeStore(
            $redis,
            $settings,
            new LoginCodeReadiness([
                'settings' => $settings,
                'redis' => $redis,
                'clock' => static fn (): int => $nowMilliseconds,
            ]),
        );

        // The old user_linked contract is latest-one-code: the historical
        // row must not become valid merely because it is still present.
        $this->assertSame('miss', $store->resolve($olderCode)['outcome']);
        $this->assertSame('hit', $store->resolve($latestCode)['outcome']);
        $this->assertSame(42, $store->resolve($latestCode)['user_id']);
        $this->assertSame('not_found', $store->status(42, $olderCode)['reason']);
        $this->assertSame('active', $store->status(42, $latestCode)['reason']);

        // database/database must not issue/read a Code_Record, call Redis
        // TIME, or invoke the login-code readiness path (which would issue
        // Redis TIME).  The separate HTTP limiter harness covers the
        // controller's no-new-limiter condition for this same mode.
        $this->assertSame([], $redis->commandCalls());
    }

    public function testDualReadKeepsLatestLegacyCodeWhileRedisReadMakesBothIssuedCodesAuthoritative(): void
    {
        $database = $this->dualWriteDatabase();
        $database->createCommand('INSERT INTO "user" (id) VALUES (42)')->execute();

        $nowMilliseconds = (int) round(microtime(true) * 1000);
        $redis = new ModeMatrixLoginCodeRedis($nowMilliseconds);
        $dualSettings = new LoginCodeSettings([
            'readMode' => LoginCodeSettings::READ_DATABASE,
            'writeMode' => LoginCodeSettings::WRITE_DUAL,
            'prefix' => 'test:mode-matrix:v1',
        ]);
        $codes = [str_repeat('c', 64), str_repeat('d', 64)];
        $dualStore = new LoginCodeStore(
            $redis,
            $dualSettings,
            new LoginCodeReadiness([
                'settings' => $dualSettings,
                'redis' => $redis,
                'clock' => static fn (): int => $nowMilliseconds,
            ]),
            static function () use (&$codes): string {
                return array_shift($codes) ?? '';
            },
        );

        $first = $dualStore->issue(42, ['source' => 'mode-matrix']);
        $second = $dualStore->issue(42, ['source' => 'mode-matrix']);

        $this->assertNotSame($first['key'], $second['key']);
        $this->assertSame(2, $redis->recordCount());
        $this->assertTrue($redis->hasRecordFor('test:mode-matrix:v1', $first['key']));
        $this->assertTrue($redis->hasRecordFor('test:mode-matrix:v1', $second['key']));
        $this->assertSame(
            hash('sha256', $second['key']),
            $database->createCommand('SELECT key FROM user_linked WHERE user_id = 42')->queryScalar(),
        );

        $redisCallsBeforeDualReads = $redis->commandCalls();
        // database/dual deliberately keeps the legacy *read* contract even
        // though both immutable Code_Records were written as a shadow.
        $this->assertSame('miss', $dualStore->resolve($first['key'])['outcome']);
        $this->assertSame('hit', $dualStore->resolve($second['key'])['outcome']);
        $this->assertSame($redisCallsBeforeDualReads, $redis->commandCalls());

        $redisSettings = new LoginCodeSettings([
            'readMode' => LoginCodeSettings::READ_REDIS,
            'writeMode' => LoginCodeSettings::WRITE_REDIS,
            'prefix' => 'test:mode-matrix:v1',
        ]);
        $redisStore = new LoginCodeStore(
            $redis,
            $redisSettings,
            new LoginCodeReadiness([
                'settings' => $redisSettings,
                'redis' => $redis,
                'clock' => static fn (): int => $nowMilliseconds,
            ]),
        );

        // Redis is the authoritative resolver only after the read switch:
        // both independently issued records remain valid together.
        $this->assertSame('hit', $redisStore->resolve($first['key'])['outcome']);
        $this->assertSame(42, $redisStore->resolve($first['key'])['user_id']);
        $this->assertSame('hit', $redisStore->resolve($second['key'])['outcome']);
        $this->assertSame(42, $redisStore->resolve($second['key'])['user_id']);
    }

    public function testConfigurationRollbackToDatabaseDatabaseRestoresLegacyResolutionWithoutRedis(): void
    {
        $database = $this->legacyDatabase();
        $legacyCode = str_repeat('e', 64);
        $this->insertLegacyRow($database, 1, 42, $legacyCode, date('Y-m-d H:i:s'));

        $redis = new ModeMatrixLoginCodeRedis(1_780_000_000_000);
        $beforeRollback = new LoginCodeSettings([
            'readMode' => LoginCodeSettings::READ_REDIS,
            'writeMode' => LoginCodeSettings::WRITE_REDIS,
            'legacyDbAvailable' => false,
        ]);
        $afterRollback = new LoginCodeSettings([
            'readMode' => LoginCodeSettings::READ_DATABASE,
            'writeMode' => LoginCodeSettings::WRITE_DATABASE,
        ]);

        $this->assertTrue($beforeRollback->usesRedis());
        $this->assertFalse($beforeRollback->legacyDbAvailable());
        $this->assertFalse($afterRollback->usesRedis());
        $this->assertTrue($afterRollback->legacyDbAvailable());

        $store = new LoginCodeStore(
            $redis,
            $afterRollback,
            new LoginCodeReadiness([
                'settings' => $afterRollback,
                'redis' => $redis,
                'clock' => static fn (): int => 1_780_000_000_000,
            ]),
        );

        $this->assertSame('hit', $store->resolve($legacyCode)['outcome']);
        $this->assertSame([], $redis->commandCalls());
    }

    private function legacyDatabase(): Connection
    {
        $database = new Connection(['dsn' => 'sqlite::memory:']);
        Yii::$app->set('db', $database);
        $database->open();
        $database->createCommand(
            'CREATE TABLE user_linked ('
            . 'id INTEGER PRIMARY KEY, '
            . 'user_id INTEGER NOT NULL, '
            . 'key TEXT NOT NULL, '
            . 'created_at TEXT NOT NULL'
            . ')',
        )->execute();

        return $database;
    }

    private function dualWriteDatabase(): ModeMatrixDualSqliteDatabase
    {
        $database = new ModeMatrixDualSqliteDatabase([
            'dsn' => 'sqlite::memory:',
            'commandClass' => ModeMatrixDualSqliteCommand::class,
        ]);
        Yii::$app->set('db', $database);
        $database->open();
        $database->createCommand('CREATE TABLE "user" (id INTEGER PRIMARY KEY)')->execute();
        $database->createCommand(
            'CREATE TABLE user_linked ('
            . 'id INTEGER PRIMARY KEY, '
            . 'user_id INTEGER NOT NULL, '
            . 'key TEXT NOT NULL, '
            . 'created_at TEXT NOT NULL'
            . ')',
        )->execute();

        return $database;
    }

    private function insertLegacyRow(
        Connection $database,
        int $id,
        int $userId,
        string $rawCode,
        string $createdAt,
    ): void {
        $database->createCommand()->insert('user_linked', [
            'id' => $id,
            'user_id' => $userId,
            'key' => hash('sha256', $rawCode),
            'created_at' => $createdAt,
        ])->execute();
    }
}

/**
 * Exact-key test adapter for the main API Store.  No wildcard operation is
 * implemented, so a future mode regression cannot silently introduce one.
 */
final class ModeMatrixLoginCodeRedis
{
    /** @var array<string, array{payload: string, pttl: int}> */
    private array $records = [];

    /** @var list<array{command: string, arguments: array<int, mixed>}> */
    private array $calls = [];

    public function __construct(private readonly int $nowMilliseconds)
    {
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    public function executeCommand(string $command, array $arguments)
    {
        $command = strtoupper($command);
        $this->calls[] = ['command' => $command, 'arguments' => $arguments];

        return match ($command) {
            'TIME' => [
                (string) intdiv($this->nowMilliseconds, 1000),
                (string) (($this->nowMilliseconds % 1000) * 1000),
            ],
            'SET' => $this->set($arguments),
            'GET' => $this->records[(string) $arguments[0]]['payload'] ?? null,
            'PTTL' => $this->records[(string) $arguments[0]]['pttl'] ?? -2,
            'DEL' => $this->delete((string) $arguments[0]),
            default => throw new \RuntimeException('Unexpected Redis command: ' . $command),
        };
    }

    /** @return list<array{command: string, arguments: array<int, mixed>}> */
    public function commandCalls(): array
    {
        return $this->calls;
    }

    public function recordCount(): int
    {
        return count($this->records);
    }

    public function hasRecordFor(string $prefix, string $rawCode): bool
    {
        return array_key_exists($prefix . ':code:' . hash('sha256', $rawCode), $this->records);
    }

    /** @param array<int, mixed> $arguments */
    private function set(array $arguments): ?string
    {
        $key = (string) $arguments[0];
        if (array_key_exists($key, $this->records)) {
            return null;
        }

        $this->records[$key] = [
            'payload' => (string) $arguments[1],
            'pttl' => (int) $arguments[3],
        ];

        return 'OK';
    }

    private function delete(string $key): int
    {
        if (!array_key_exists($key, $this->records)) {
            return 0;
        }

        unset($this->records[$key]);

        return 1;
    }
}

/**
 * SQLite cannot execute MySQL's FOR UPDATE or fixed-zone timestamp expression.
 * This narrow adapter rewrites only those two production fragments after the
 * production Store has built its SQL, allowing the successful dual path to be
 * verified without a develop/production database.
 */
final class ModeMatrixDualSqliteDatabase extends Connection
{
    public function createCommand($sql = null, $params = [])
    {
        if (is_string($sql)) {
            $sql = preg_replace('/\s+FOR\s+UPDATE\s*$/i', '', $sql) ?? $sql;
        }

        return parent::createCommand($sql, $params);
    }
}

final class ModeMatrixDualSqliteCommand extends \yii\db\sqlite\Command
{
    public function execute()
    {
        $sql = $this->getSql();
        if (is_string($sql) && str_contains($sql, 'CONVERT_TZ(DATE_ADD(')) {
            $params = $this->params;
            $rewritten = preg_replace(
                "~CONVERT_TZ\\(DATE_ADD\\('1970-01-01 00:00:00', INTERVAL\\s+:loginCodeIssuedAt\\s+SECOND\\),\\s*'\\+00:00',\\s*'\\+08:00'\\)~",
                "datetime(:loginCodeIssuedAt, 'unixepoch')",
                $sql,
            );
            if (!is_string($rewritten) || $rewritten === $sql) {
                throw new \RuntimeException('Unable to normalize the test-only legacy timestamp expression.');
            }
            $this->setSql($rewritten)->bindValues($params);
        }

        return parent::execute();
    }
}

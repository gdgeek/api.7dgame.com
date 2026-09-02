<?php

namespace tests\integration;

use api\modules\v1\services\DbTask51StageBRepository;
use api\modules\v1\services\Task51CanonicalArtifact;
use api\modules\v1\services\Task51CoordinatorException;
use api\modules\v1\services\Task51StageBCoordinatorService;
use console\components\Task51AppliedSchemaFingerprint;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use tests\support\Task51ArtifactFixture;
use Throwable;
use yii\db\Connection;
use yii\db\Exception as DbException;

require_once dirname(__DIR__) . '/support/Task51ArtifactFixture.php';

/**
 * Opt-in destructive suite for a dedicated, disposable supported MySQL database.
 *
 * It has no fallback to the application's DB configuration. To run it, set:
 *   TASK51_MYSQL_INTEGRATION=1
 *   TASK51_MYSQL_TEST_DSN=mysql:host=...;dbname=task51_test_...
 *   TASK51_MYSQL_TEST_USER=...
 *   TASK51_MYSQL_TEST_PASSWORD=...
 *
 * @group integration
 * @group task51-mysql
 */
final class Task51StageBMySqlCasTest extends TestCase
{
    private const CAPABILITY = Task51ArtifactFixture::CAPABILITY;
    private const SERVER_SHA = Task51ArtifactFixture::SERVER_SHA;
    private const EXECUTION_TABLE = 'task51_stage_b_execution';
    private const TRANSITION_TABLE = 'task51_stage_b_transition';
    private const MIGRATION_TABLE = 'migration';
    private const EXACT_MIGRATION = 'm260828_170000_create_task51_stage_b_coordinator_tables';

    /** @var array<string, mixed> */
    private array $connectionConfig = [];
    private ?Connection $db = null;
    private bool $ownsSchema = false;
    private string $failureTrigger = '';
    private bool $failureTriggerCreated = false;
    private string $schemaLockName = '';
    private bool $schemaLockHeld = false;
    private bool $ownsMigrationTable = false;
    private bool $historyRowInserted = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('TASK51_MYSQL_INTEGRATION') !== '1') {
            $this->markTestSkipped(
                'Set TASK51_MYSQL_INTEGRATION=1 only with an isolated task51_test_* MySQL database.'
            );
        }

        $this->connectionConfig = $this->isolatedConnectionConfig();
        $this->db = $this->newConnection();
        $this->assertSupportedIsolatedServer($this->db);
        $this->acquireSchemaLock();
        $this->assertTablesAbsent($this->db);
        $this->failureTrigger = 'task51_test_fail_' . bin2hex(random_bytes(6));

        // From here on tearDown may remove only the two tables in the guarded,
        // dedicated test database, including a partial implicit-commit DDL run.
        $this->ownsSchema = true;
        $this->installMigrationHistoryFixtureTable();
        $this->assertNull($this->migration($this->db)->up());
        $this->insertMigrationHistoryFixtureRow();
        $this->db->getSchema()->refresh();
    }

    protected function tearDown(): void
    {
        if ($this->db instanceof Connection) {
            try {
                if ($this->ownsSchema) {
                    $this->db->open();
                    if ($this->failureTriggerCreated && $this->failureTrigger !== '') {
                        $this->db->createCommand(
                            'DROP TRIGGER IF EXISTS `' . $this->failureTrigger . '`'
                        )->execute();
                    }
                    $this->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
                    $this->db->createCommand('DROP TABLE IF EXISTS `' . self::TRANSITION_TABLE . '`')->execute();
                    $this->db->createCommand('DROP TABLE IF EXISTS `' . self::EXECUTION_TABLE . '`')->execute();
                    $this->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
                    if ($this->historyRowInserted && !$this->ownsMigrationTable) {
                        $this->db->createCommand()->delete(
                            self::MIGRATION_TABLE,
                            ['version' => self::EXACT_MIGRATION]
                        )->execute();
                    }
                    if ($this->ownsMigrationTable) {
                        $this->db->createCommand(
                            'DROP TABLE IF EXISTS `' . self::MIGRATION_TABLE . '`'
                        )->execute();
                    }
                }
            } finally {
                $this->releaseSchemaLock();
                $this->db->close();
            }
        }
        parent::tearDown();
    }

    public function testMigrationLifecycleChecksAndIrreversibleRollbackGuard(): void
    {
        $this->assertSame(Connection::class, get_class($this->db()));
        $this->assertSame(\yii\db\Command::class, $this->db()->commandClass);
        $this->assertFalse($this->db()->enableSlaves);
        $this->assertFalse($this->migration($this->db())->safeDown());
        $this->assertNotNull($this->db()->getSchema()->getTableSchema(self::EXECUTION_TABLE, true));
        $this->assertNotNull($this->db()->getSchema()->getTableSchema(self::TRANSITION_TABLE, true));
        $status = $this->db()->createCommand(
            'SHOW TABLE STATUS WHERE Name = :name',
            [':name' => self::EXECUTION_TABLE]
        )->queryOne();
        $this->assertIsArray($status);
        $this->assertSame('InnoDB', $status['Engine']);
        $this->assertSame('ascii_bin', $status['Collation']);

        $create = $this->db()->createCommand('SHOW CREATE TABLE `' . self::EXECUTION_TABLE . '`')->queryOne();
        $createSql = is_array($create) ? (string)array_values($create)[1] : '';
        $this->assertStringContainsString('ck_task51_stage_b_counts', $createSql);
        $this->assertStringContainsString('ck_task51_stage_b_receipts', $createSql);
        $this->assertStringContainsString('ck_task51_stage_b_time_order', $createSql);

        [, $rawStageB] = $this->issuedFixture();
        $this->assertMySqlCheckViolation(function () use ($rawStageB): void {
            $this->db()->createCommand()->update(
                self::EXECUTION_TABLE,
                ['claim_count' => 1],
                ['execution_id' => $this->stageB($rawStageB)['executionId']]
            )->execute();
        });
        $this->assertMySqlCheckViolation(function () use ($rawStageB): void {
            $this->db()->createCommand()->update(
                self::EXECUTION_TABLE,
                [
                    'state' => 'CLAIMED',
                    'state_version' => 1,
                    'claim_count' => 1,
                    'claimed_at' => $this->executionRow()['issued_at'],
                ],
                ['execution_id' => $this->stageB($rawStageB)['executionId']]
            )->execute();
        });
        $this->assertMySqlCheckViolation(function () use ($rawStageB): void {
            $this->db()->createCommand(
                'UPDATE `' . self::EXECUTION_TABLE . '` '
                    . 'SET created_at = DATE_SUB(issued_at, INTERVAL 1 SECOND) WHERE execution_id = :id',
                [':id' => $this->stageB($rawStageB)['executionId']]
            )->execute();
        });
        $executionId = $this->stageB($rawStageB)['executionId'];
        $occurredAt = $this->executionRow()['issued_at'];
        $this->assertMySqlCheckViolation(function () use ($executionId, $occurredAt): void {
            $this->db()->createCommand()->insert(self::TRANSITION_TABLE, [
                'execution_id' => $executionId,
                'ordinal' => 1,
                'from_state' => null,
                'to_state' => 'CLAIMED',
                'state_version' => 1,
                'evidence_sha256' => str_repeat('a', 64),
                'occurred_at' => $occurredAt,
            ])->execute();
        });
        $this->assertMySqlAppendOnlyViolation(function () use ($executionId): void {
            $this->db()->createCommand()->update(
                self::TRANSITION_TABLE,
                ['evidence_sha256' => str_repeat('b', 64)],
                ['execution_id' => $executionId, 'ordinal' => 0]
            )->execute();
        });
        $this->assertMySqlAppendOnlyViolation(function () use ($executionId): void {
            $this->db()->createCommand()->delete(
                self::TRANSITION_TABLE,
                ['execution_id' => $executionId, 'ordinal' => 0]
            )->execute();
        });
        $this->assertMySqlCheckViolation(function () use ($executionId, $occurredAt): void {
            $this->db()->createCommand()->insert(self::TRANSITION_TABLE, [
                'execution_id' => $executionId,
                'ordinal' => 2,
                'from_state' => 'ISSUED',
                'to_state' => 'CONSUMED',
                'state_version' => 2,
                'evidence_sha256' => str_repeat('a', 64),
                'occurred_at' => $occurredAt,
            ])->execute();
        });
        $this->assertMySqlCheckViolation(function () use ($executionId, $occurredAt): void {
            $this->db()->createCommand()->insert(self::TRANSITION_TABLE, [
                'execution_id' => $executionId,
                'ordinal' => 2,
                'from_state' => 'CLAIMED',
                'to_state' => 'CONSUMED',
                'state_version' => 2,
                'evidence_sha256' => str_repeat('A', 64),
                'occurred_at' => $occurredAt,
            ])->execute();
        });

        $this->assertFalse($this->migration($this->db())->safeDown());
        $this->assertNotNull($this->db()->getSchema()->getTableSchema(self::EXECUTION_TABLE, true));
        $this->assertNotNull($this->db()->getSchema()->getTableSchema(self::TRANSITION_TABLE, true));
    }

    public function testAppliedSchemaFingerprintAcceptsExactMigrationAndIsStable(): void
    {
        $fingerprint = new Task51AppliedSchemaFingerprint();
        $first = $fingerprint->assertAlreadyApplied(
            $this->db(),
            '{{%migration}}',
            self::EXACT_MIGRATION
        );
        $second = $fingerprint->assertAlreadyApplied(
            $this->db(),
            '{{%migration}}',
            self::EXACT_MIGRATION
        );

        $this->assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/D', $first);
        $this->assertSame($first, $second);
    }

    public function testAppliedSchemaFingerprintBoundsMetadataAndHistoryRowLocks(): void
    {
        Task51AppliedSchemaFingerprint::assertSupportedDatabase($this->db());

        $this->assertSame(20, (int)$this->db()->createCommand(
            'SELECT @@SESSION.lock_wait_timeout'
        )->queryScalar());
        $this->assertSame(20, (int)$this->db()->createCommand(
            'SELECT @@SESSION.innodb_lock_wait_timeout'
        )->queryScalar());
    }

    public function testRepositoryTransactionOverridesReadCommittedSession(): void
    {
        [, $rawStageB] = $this->issuedFixture();
        $executionId = $this->stageB($rawStageB)['executionId'];
        $updatedApprovalRef = 'task51-rr-' . bin2hex(random_bytes(16));
        $writer = $this->newConnection();
        $this->db()->createCommand(
            'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED'
        )->execute();
        $repository = new DbTask51StageBRepository($this->db());

        try {
            [$before, $after] = $repository->transaction(function () use (
                $executionId,
                $updatedApprovalRef,
                $writer
            ): array {
                $before = (string)$this->db()->createCommand(
                    'SELECT approval_ref FROM `' . self::EXECUTION_TABLE
                        . '` WHERE execution_id = :executionId',
                    [':executionId' => $executionId]
                )->queryScalar();
                $this->assertSame(1, $writer->createCommand()->update(
                    self::EXECUTION_TABLE,
                    ['approval_ref' => $updatedApprovalRef],
                    ['execution_id' => $executionId]
                )->execute());
                $after = (string)$this->db()->createCommand(
                    'SELECT approval_ref FROM `' . self::EXECUTION_TABLE
                        . '` WHERE execution_id = :executionId',
                    [':executionId' => $executionId]
                )->queryScalar();

                return [$before, $after];
            });
        } finally {
            $writer->close();
        }

        $this->assertSame($before, $after);
        $this->assertNotSame($updatedApprovalRef, $after);
        $this->assertSame($updatedApprovalRef, (string)$this->db()->createCommand(
            'SELECT approval_ref FROM `' . self::EXECUTION_TABLE
                . '` WHERE execution_id = :executionId',
            [':executionId' => $executionId]
        )->queryScalar());
    }

    public function testAppliedSchemaFingerprintRejectsMyIsamMigrationHistory(): void
    {
        $prefix = 'task51_myisam_';
        $table = $prefix . self::MIGRATION_TABLE;
        $config = $this->connectionConfig;
        $config['tablePrefix'] = $prefix;
        $db = new Connection($config);
        $db->open();
        try {
            if ($db->getSchema()->getTableSchema($table, true) !== null) {
                throw new RuntimeException(
                    'Refusing to overwrite the isolated MyISAM migration-history fixture.'
                );
            }
            $db->createCommand(
                'CREATE TABLE `' . $table . '` ('
                    . '`version` varchar(180) NOT NULL PRIMARY KEY, '
                    . '`apply_time` integer NOT NULL) ENGINE=MyISAM'
            )->execute();

            try {
                (new Task51AppliedSchemaFingerprint())->assertMigrationHistoryTable(
                    $db,
                    '{{%migration}}'
                );
                $this->fail('A MyISAM migration-history table must fail before DDL.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString(
                    'migration-history table must be an exact InnoDB base table',
                    $exception->getMessage()
                );
            }
            $this->assertNull($db->getSchema()->getTableSchema(
                $prefix . self::EXECUTION_TABLE,
                true
            ));
            $this->assertNull($db->getSchema()->getTableSchema(
                $prefix . self::TRANSITION_TABLE,
                true
            ));
        } finally {
            $db->createCommand('DROP TABLE IF EXISTS `' . $table . '`')->execute();
            $db->close();
        }
    }

    public function testAppliedSchemaFingerprintRejectsExtraIndex(): void
    {
        $this->db()->createCommand(
            'CREATE INDEX `idx_task51_test_extra` ON `'
                . self::EXECUTION_TABLE . '` (`updated_at`)'
        )->execute();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('schema fingerprint mismatch');
        (new Task51AppliedSchemaFingerprint())->assertAlreadyApplied(
            $this->db(),
            '{{%migration}}',
            self::EXACT_MIGRATION
        );
    }

    public function testAppliedSchemaFingerprintRejectsMissingAppendOnlyTrigger(): void
    {
        $this->db()->createCommand(
            'DROP TRIGGER `trg_task51_stage_b_transition_no_update`'
        )->execute();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('schema fingerprint mismatch');
        (new Task51AppliedSchemaFingerprint())->assertAlreadyApplied(
            $this->db(),
            '{{%migration}}',
            self::EXACT_MIGRATION
        );
    }

    public function testAppliedSchemaFingerprintRejectsNonEnforcedCheck(): void
    {
        $this->db()->createCommand(
            'ALTER TABLE `' . self::EXECUTION_TABLE
                . '` ALTER CHECK `ck_task51_stage_b_state` NOT ENFORCED'
        )->execute();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('schema fingerprint mismatch');
        (new Task51AppliedSchemaFingerprint())->assertAlreadyApplied(
            $this->db(),
            '{{%migration}}',
            self::EXACT_MIGRATION
        );
    }

    public function testAppliedSchemaFingerprintRejectsChangedForeignKeyRule(): void
    {
        $this->db()->createCommand(
            'ALTER TABLE `' . self::TRANSITION_TABLE
                . '` DROP FOREIGN KEY `fk_task51_stage_b_transition_execution`'
        )->execute();
        $this->db()->createCommand(
            'ALTER TABLE `' . self::TRANSITION_TABLE . '` '
                . 'ADD CONSTRAINT `fk_task51_stage_b_transition_execution` '
                . 'FOREIGN KEY (`execution_id`) REFERENCES `'
                . self::EXECUTION_TABLE . '` (`execution_id`) '
                . 'ON DELETE CASCADE ON UPDATE RESTRICT'
        )->execute();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('schema fingerprint mismatch');
        (new Task51AppliedSchemaFingerprint())->assertAlreadyApplied(
            $this->db(),
            '{{%migration}}',
            self::EXACT_MIGRATION
        );
    }

    public function testFingerprintMetadataLocksBoundConcurrentDdl(): void
    {
        $transaction = $this->db()->beginTransaction(\yii\db\Transaction::REPEATABLE_READ);
        $this->db()->createCommand(
            'SELECT 1 FROM `' . self::EXECUTION_TABLE . '` LIMIT 0'
        )->queryAll();
        $this->db()->createCommand(
            'SELECT 1 FROM `' . self::TRANSITION_TABLE . '` LIMIT 0'
        )->queryAll();
        $contender = $this->newConnection();
        $contender->createCommand('SET SESSION lock_wait_timeout = 1')->execute();
        try {
            try {
                $contender->createCommand(
                    'ALTER TABLE `' . self::EXECUTION_TABLE
                        . '` ADD COLUMN `task51_test_forbidden_during_fingerprint` INT NULL'
                )->execute();
                $this->fail('Concurrent DDL must wait behind the fingerprint metadata lock.');
            } catch (DbException $exception) {
                $this->assertSame(1205, (int)($exception->errorInfo[1] ?? 0));
            }
        } finally {
            $contender->close();
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
        }
        $this->assertNull($this->db()->getSchema()->getTableSchema(
            self::EXECUTION_TABLE,
            true
        )->getColumn('task51_test_forbidden_during_fingerprint'));
    }

    public function testTwoIndependentProcessesProduceExactlyOneClaimWinner(): void
    {
        $this->requireForkSupport();
        [, $rawStageB] = $this->issuedFixture();
        $paths = $this->temporaryRaceFiles(2);
        $results = [];
        $children = [];
        $waited = [];
        $this->releaseSchemaLock();
        $this->db()->close();

        try {
            for ($index = 0; $index < 2; $index++) {
                $pid = pcntl_fork();
                if ($pid === -1) {
                    throw new RuntimeException('Unable to fork MySQL claim contender.');
                }
                if ($pid === 0) {
                    $this->runClaimChild(
                        $paths['gate'],
                        $paths['results'][$index],
                        $rawStageB,
                        $paths['ready'][$index]
                    );
                }
                $children[] = $pid;
            }
            $this->db()->open();
            $this->acquireSchemaLock();
            $this->waitForReady($paths['ready']);
            file_put_contents($paths['gate'], 'go', LOCK_EX);
            foreach ($children as $child) {
                $status = $this->waitForChild($child);
                $waited[$child] = true;
                $this->assertTrue(pcntl_wifexited($status));
                $this->assertSame(0, pcntl_wexitstatus($status));
            }
            $results = array_map(
                static fn(string $path): array => json_decode(
                    (string)file_get_contents($path),
                    true,
                    8,
                    JSON_THROW_ON_ERROR
                ),
                $paths['results']
            );
        } finally {
            if (is_file($paths['gate'])) {
                file_put_contents($paths['gate'], 'go', LOCK_EX);
            }
            foreach ($children as $child) {
                if (!isset($waited[$child])) {
                    try {
                        $this->waitForChild($child);
                    } catch (RuntimeException) {
                    }
                }
            }
            $this->removeRaceFiles($paths);
            $this->db()->open();
            if (!$this->schemaLockHeld) {
                $this->acquireSchemaLock();
            }
        }

        $statuses = array_column($results, 'status');
        sort($statuses);
        $this->assertSame(['conflict', 'success'], $statuses);

        $row = $this->executionRow();
        $this->assertSame('CLAIMED', $row['state']);
        $this->assertSame(1, (int)$row['claim_count']);
        $this->assertSame(1, (int)$row['state_version']);
        $this->assertSame(1, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::TRANSITION_TABLE . '` WHERE ordinal = 1'
        )->queryScalar());
    }

    public function testTwoIndependentProcessesDuplicateIssueIsIdempotent(): void
    {
        $this->requireForkSupport();
        $now = $this->repository()->now();
        $rawStageB = $this->rawStageB(
            $now->sub(new DateInterval('PT1M')),
            $now->add(new DateInterval('PT1H'))
        );

        $results = $this->runConcurrentOperations([
            ['operation' => 'issue', 'body' => $rawStageB, 'evidenceRef' => null],
            ['operation' => 'issue', 'body' => $rawStageB, 'evidenceRef' => null],
        ]);
        $this->assertSame(['success', 'success'], array_column($results, 'status'));
        $this->assertSame(1, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::EXECUTION_TABLE . '`'
        )->queryScalar());
        $this->assertSame(1, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::TRANSITION_TABLE . '` WHERE ordinal = 0'
        )->queryScalar());
    }

    public function testTwoIndependentProcessesProduceExactlyOneConsumeWinner(): void
    {
        $this->requireForkSupport();
        [$service, $rawStageB] = $this->issuedFixture();
        $service->claim($rawStageB, self::CAPABILITY);
        $first = $this->rawRunnerExport($rawStageB);
        $secondValue = Task51CanonicalArtifact::parseRunnerExport($first);
        $secondValue['runnerResultEvidenceSha256'] = str_repeat('f', 64);
        $second = Task51CanonicalArtifact::encode(
            $secondValue,
            Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES
        );

        $results = $this->runConcurrentOperations([
            ['operation' => 'consume', 'body' => $first, 'evidenceRef' => 'reports/task51-export.json'],
            ['operation' => 'consume', 'body' => $second, 'evidenceRef' => 'reports/task51-export.json'],
        ]);
        $statuses = array_column($results, 'status');
        sort($statuses);
        $this->assertSame(['conflict', 'success'], $statuses);
        $row = $this->executionRow();
        $this->assertSame('CONSUMED', $row['state']);
        $this->assertSame(1, (int)$row['consumption_count']);
        $this->assertSame(1, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::TRANSITION_TABLE . '` WHERE ordinal = 2'
        )->queryScalar());
    }

    public function testNewServerShaCannotConsumeOrReplayOldReleaseRow(): void
    {
        [$currentService, $rawStageB] = $this->issuedFixture();
        $currentService->claim($rawStageB, self::CAPABILITY);
        $rawExport = $this->rawRunnerExport($rawStageB);
        $newReleaseService = $this->service(null, str_repeat('b', 40));

        try {
            $newReleaseService->consume($rawExport, 'reports/task51-export.json');
            $this->fail('A new release must not consume an old release row.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::INVALID, $exception->reason());
        }
        $this->assertSame('CLAIMED', $this->executionRow()['state']);

        $receipt = $currentService->consume($rawExport, 'reports/task51-export.json');
        $this->assertNotSame('', $receipt);
        try {
            $newReleaseService->consume($rawExport, 'reports/task51-export.json');
            $this->fail('A new release must not replay an old release receipt.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::INVALID, $exception->reason());
        }
    }

    public function testClaimSamplesClockAfterWaitingForRowLock(): void
    {
        $this->requireForkSupport();
        $now = $this->repository()->now();
        $rawStageB = $this->rawStageB(
            $now->sub(new DateInterval('PT5M')),
            $now->add(new DateInterval('PT30M25S'))
        );
        $this->service()->issue($rawStageB, self::CAPABILITY);

        $paths = $this->temporaryRaceFiles(1);
        $result = [];
        $childWaited = false;
        $this->releaseSchemaLock();
        $this->db()->close();
        $pid = -1;
        $locker = null;
        $transaction = null;
        try {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new RuntimeException('Unable to fork row-lock waiter.');
            }
            if ($pid === 0) {
                $this->runClaimChild(
                    $paths['gate'],
                    $paths['results'][0],
                    $rawStageB,
                    $paths['ready'][0]
                );
            }
            $this->db()->open();
            $this->acquireSchemaLock();
            $ready = $this->waitForReady($paths['ready']);
            $locker = $this->newConnection();
            $transaction = $locker->beginTransaction();
            $locker->createCommand(
                'SELECT execution_id FROM `' . self::EXECUTION_TABLE . '` WHERE execution_id = :id FOR UPDATE',
                [':id' => $this->stageB($rawStageB)['executionId']]
            )->queryScalar();
            file_put_contents($paths['gate'], 'go', LOCK_EX);
            $this->waitForRowLockWait((int)$ready[0]['connectionId']);
            $expiresAt = Task51CanonicalArtifact::parseTimestamp($this->stageB($rawStageB)['expiresAt']);
            $lastPermittedClaimAt = $expiresAt->sub(new DateInterval('PT30M15S'));
            $lockedNow = $this->repository($locker)->now();
            $this->assertLessThan(
                $lastPermittedClaimAt,
                $lockedNow,
                'The child must begin waiting while the claim window is still valid.'
            );
            $waitMicros = (int)ceil(
                (((float)$lastPermittedClaimAt->format('U.u') - (float)$lockedNow->format('U.u')) + 0.250)
                * 1_000_000
            );
            $this->assertGreaterThan(0, $waitMicros);
            $this->assertLessThan(15_000_000, $waitMicros);
            usleep($waitMicros);
            $transaction->commit();
            $status = $this->waitForChild($pid);
            $childWaited = true;
            $this->assertTrue(pcntl_wifexited($status));
            $this->assertSame(0, pcntl_wexitstatus($status));
            $result = json_decode(
                (string)file_get_contents($paths['results'][0]),
                true,
                8,
                JSON_THROW_ON_ERROR
            );
        } finally {
            if ($transaction !== null && $transaction->isActive) {
                $transaction->rollBack();
            }
            if (!$childWaited && $pid > 0) {
                if (is_file($paths['gate'])) {
                    file_put_contents($paths['gate'], 'go', LOCK_EX);
                }
                try {
                    $this->waitForChild($pid);
                } catch (RuntimeException) {
                }
            }
            if ($locker instanceof Connection) {
                $locker->close();
            }
            $this->removeRaceFiles($paths);
            $this->db()->open();
            if (!$this->schemaLockHeld) {
                $this->acquireSchemaLock();
            }
        }

        $this->assertSame('expired', $result['status']);
        $this->assertSame('ISSUED', $this->executionRow()['state']);
        $this->assertSame(0, (int)$this->executionRow()['claim_count']);
    }

    public function testTransitionInsertFailureRollsBackClaimAtomically(): void
    {
        [$service, $rawStageB] = $this->issuedFixture();
        $this->installFailureTrigger(1);

        try {
            $service->claim($rawStageB, self::CAPABILITY);
            $this->fail('The injected transition failure must abort claim.');
        } catch (DbException $exception) {
            $this->assertStringContainsString('forced Task51 transition failure', $exception->getMessage());
        } finally {
            $this->dropFailureTrigger();
        }

        $row = $this->executionRow();
        $this->assertSame('ISSUED', $row['state']);
        $this->assertSame(0, (int)$row['claim_count']);
        $this->assertNull($row['claim_receipt_canonical']);
        $this->assertSame(1, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::TRANSITION_TABLE . '`'
        )->queryScalar());
        $this->assertNotSame('', $service->claim($rawStageB, self::CAPABILITY));
    }

    public function testTransitionInsertFailureRollsBackIssueAtomically(): void
    {
        $now = $this->repository()->now();
        $rawStageB = $this->rawStageB(
            $now->sub(new DateInterval('PT1M')),
            $now->add(new DateInterval('PT1H'))
        );
        $this->installFailureTrigger(0);
        try {
            $this->service()->issue($rawStageB, self::CAPABILITY);
            $this->fail('The injected transition failure must abort issue.');
        } catch (DbException $exception) {
            $this->assertStringContainsString('forced Task51 transition failure', $exception->getMessage());
        } finally {
            $this->dropFailureTrigger();
        }
        $this->assertSame(0, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::EXECUTION_TABLE . '`'
        )->queryScalar());
        $this->assertSame(0, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::TRANSITION_TABLE . '`'
        )->queryScalar());
        $this->assertSame('ISSUED', $this->service()->issue($rawStageB, self::CAPABILITY)['state']);
    }

    public function testTransitionInsertFailureRollsBackConsumeAtomically(): void
    {
        [$service, $rawStageB] = $this->issuedFixture();
        $service->claim($rawStageB, self::CAPABILITY);
        $rawExport = $this->rawRunnerExport($rawStageB);
        $this->installFailureTrigger(2);
        try {
            $service->consume($rawExport, 'reports/task51-export.json');
            $this->fail('The injected transition failure must abort consume.');
        } catch (DbException $exception) {
            $this->assertStringContainsString('forced Task51 transition failure', $exception->getMessage());
        } finally {
            $this->dropFailureTrigger();
        }
        $row = $this->executionRow();
        $this->assertSame('CLAIMED', $row['state']);
        $this->assertSame(0, (int)$row['consumption_count']);
        $this->assertNull($row['consumption_receipt_canonical']);
        $this->assertSame(2, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::TRANSITION_TABLE . '`'
        )->queryScalar());
        $this->assertNotSame('', $service->consume($rawExport, 'reports/task51-export.json'));
    }

    public function testIssueAndConsumeIdempotencyAndConflictsUseStoredAuthority(): void
    {
        [$service, $rawStageB] = $this->issuedFixture();
        $reissued = $service->issue($rawStageB, self::CAPABILITY);
        $this->assertSame('ISSUED', $reissued['state']);
        $this->assertSame(1, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::TRANSITION_TABLE . '` WHERE ordinal = 0'
        )->queryScalar());

        $changed = $this->stageB($rawStageB);
        $changed['executionId'] = 'task51-stage-b-' . bin2hex(random_bytes(8));
        try {
            $service->issue(
                Task51CanonicalArtifact::encode($changed, Task51CanonicalArtifact::MAX_STAGE_B_BYTES),
                self::CAPABILITY
            );
            $this->fail('A different execution cannot reuse an approval ref.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::CONFLICT, $exception->reason());
        }

        $service->claim($rawStageB, self::CAPABILITY);
        $rawExport = $this->rawRunnerExport($rawStageB);
        $receipt = $service->consume($rawExport, 'reports/task51-export.json');
        $this->assertSame($receipt, $service->consume($rawExport, 'reports/task51-export.json'));
        try {
            $service->consume($rawExport, 'reports/task51-other-export.json');
            $this->fail('A different evidence ref cannot replace canonical C.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::CONFLICT, $exception->reason());
        }
        $differentExport = Task51CanonicalArtifact::parseRunnerExport($rawExport);
        $differentExport['runnerResultEvidenceSha256'] = str_repeat('f', 64);
        try {
            $service->consume(
                Task51CanonicalArtifact::encode(
                    $differentExport,
                    Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES
                ),
                'reports/task51-export.json'
            );
            $this->fail('A different E cannot replace canonical C.');
        } catch (Task51CoordinatorException $exception) {
            $this->assertSame(Task51CoordinatorException::CONFLICT, $exception->reason());
        }
        $this->assertSame(3, (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::TRANSITION_TABLE . '`'
        )->queryScalar());
    }

    /** @return array{0: Task51StageBCoordinatorService, 1: string} */
    private function issuedFixture(): array
    {
        $now = $this->repository()->now();
        $rawStageB = $this->rawStageB(
            $now->sub(new DateInterval('PT1M')),
            $now->add(new DateInterval('PT1H'))
        );
        $service = $this->service();
        $service->issue($rawStageB, self::CAPABILITY);
        return [$service, $rawStageB];
    }

    private function rawStageB(DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt): string
    {
        return Task51CanonicalArtifact::encode(
            Task51ArtifactFixture::stageB(
                $issuedAt,
                $expiresAt,
                'task51-stage-b-' . bin2hex(random_bytes(8))
            ),
            Task51CanonicalArtifact::MAX_STAGE_B_BYTES
        );
    }

    private function rawRunnerExport(string $rawStageB): string
    {
        $exportedAt = $this->repository()->now();
        $this->waitForDatabaseTimeAfter($exportedAt);
        return Task51CanonicalArtifact::encode(
            Task51ArtifactFixture::runnerExport($rawStageB, $exportedAt),
            Task51CanonicalArtifact::MAX_RUNNER_EXPORT_BYTES
        );
    }

    /** @return array<string, mixed> */
    private function stageB(string $rawStageB): array
    {
        return Task51CanonicalArtifact::parseStageB($rawStageB);
    }

    /** @return array<string, mixed> */
    private function executionRow(): array
    {
        $row = $this->db()->createCommand('SELECT * FROM `' . self::EXECUTION_TABLE . '` LIMIT 1')->queryOne();
        $this->assertIsArray($row);
        return $row;
    }

    private function service(
        ?Connection $db = null,
        string $serverSha = self::SERVER_SHA
    ): Task51StageBCoordinatorService
    {
        return new Task51StageBCoordinatorService(
            new DbTask51StageBRepository($db ?? $this->db()),
            $serverSha
        );
    }

    private function repository(?Connection $db = null): DbTask51StageBRepository
    {
        return new DbTask51StageBRepository($db ?? $this->db());
    }

    private function db(): Connection
    {
        if (!$this->db instanceof Connection) {
            throw new RuntimeException('Task51 integration DB is not initialized.');
        }
        return $this->db;
    }

    private function newConnection(): Connection
    {
        $db = new Connection($this->connectionConfig);
        $db->open();
        $db->createCommand("SET SESSION time_zone = '+00:00'")->execute();
        $db->createCommand('SET SESSION innodb_lock_wait_timeout = 20')->execute();
        $db->createCommand('SET SESSION lock_wait_timeout = 20')->execute();
        return $db;
    }

    private function migration(Connection $db): \m260828_170000_create_task51_stage_b_coordinator_tables
    {
        require_once dirname(__DIR__, 2)
            . '/console/migrations/m260828_170000_create_task51_stage_b_coordinator_tables.php';
        return new \m260828_170000_create_task51_stage_b_coordinator_tables(['db' => $db]);
    }

    /** @return array<string, mixed> */
    private function isolatedConnectionConfig(): array
    {
        $dsn = getenv('TASK51_MYSQL_TEST_DSN');
        $username = getenv('TASK51_MYSQL_TEST_USER');
        $password = getenv('TASK51_MYSQL_TEST_PASSWORD');
        if (!is_string($dsn) || !str_starts_with($dsn, 'mysql:')
            || !is_string($username) || $username === '' || !is_string($password)) {
            throw new RuntimeException('Explicit TASK51_MYSQL_TEST_DSN/USER/PASSWORD are required.');
        }

        return [
            'dsn' => $dsn,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'tablePrefix' => '',
            'enableSchemaCache' => false,
            'commandClass' => \yii\db\Command::class,
            'enableSlaves' => false,
            'attributes' => [\PDO::ATTR_TIMEOUT => 5],
        ];
    }

    private function assertSupportedIsolatedServer(Connection $db): void
    {
        $database = (string)$db->createCommand('SELECT DATABASE()')->queryScalar();
        if (preg_match('/^task51_test_[a-z0-9_]+$/D', $database) !== 1) {
            throw new RuntimeException(
                'Refusing destructive Task51 integration tests outside a task51_test_* database.'
            );
        }
        Task51AppliedSchemaFingerprint::assertSupportedDatabase($db);
    }

    private function assertTablesAbsent(Connection $db): void
    {
        $db->getSchema()->refresh();
        foreach ([self::EXECUTION_TABLE, self::TRANSITION_TABLE] as $table) {
            if ($db->getSchema()->getTableSchema($table, true) !== null) {
                throw new RuntimeException(
                    "Refusing to overwrite existing {$table}; use a clean isolated test database."
                );
            }
        }
    }

    private function installMigrationHistoryFixtureTable(): void
    {
        $this->db()->getSchema()->refreshTableSchema(self::MIGRATION_TABLE);
        if ($this->db()->getSchema()->getTableSchema(self::MIGRATION_TABLE, true) === null) {
            $this->db()->createCommand()->createTable(self::MIGRATION_TABLE, [
                'version' => 'varchar(180) NOT NULL PRIMARY KEY',
                'apply_time' => 'integer NOT NULL',
            ])->execute();
            $this->ownsMigrationTable = true;
        }
    }

    private function insertMigrationHistoryFixtureRow(): void
    {
        $count = (int)$this->db()->createCommand(
            'SELECT COUNT(*) FROM `' . self::MIGRATION_TABLE . '` WHERE version = :version',
            [':version' => self::EXACT_MIGRATION]
        )->queryScalar();
        if ($count !== 0) {
            throw new RuntimeException(
                'Refusing an isolated Task51 test database with an existing exact migration row.'
            );
        }
        $this->db()->createCommand()->insert(self::MIGRATION_TABLE, [
            'version' => self::EXACT_MIGRATION,
            'apply_time' => time(),
        ])->execute();
        $this->historyRowInserted = true;
    }

    private function acquireSchemaLock(): void
    {
        $database = (string)$this->db()->createCommand('SELECT DATABASE()')->queryScalar();
        $this->schemaLockName = 'task51-stage-b-' . substr(hash('sha256', $database), 0, 32);
        $acquired = (int)$this->db()->createCommand(
            'SELECT GET_LOCK(:lockName, 30)',
            [':lockName' => $this->schemaLockName]
        )->queryScalar();
        if ($acquired !== 1) {
            throw new RuntimeException('Unable to acquire the schema-scoped Task51 integration lock.');
        }
        $this->schemaLockHeld = true;
    }

    private function releaseSchemaLock(): void
    {
        if (!$this->schemaLockHeld || !$this->db instanceof Connection) {
            return;
        }
        $this->db->open();
        $this->db->createCommand(
            'SELECT RELEASE_LOCK(:lockName)',
            [':lockName' => $this->schemaLockName]
        )->queryScalar();
        $this->schemaLockHeld = false;
    }

    private function waitForDatabaseTimeAfter(DateTimeImmutable $instant): void
    {
        $deadline = microtime(true) + 3.0;
        do {
            if ($this->repository()->now() > $instant) {
                return;
            }
            usleep(1000);
        } while (microtime(true) < $deadline);
        throw new RuntimeException('Database clock did not advance beyond the runner export timestamp.');
    }

    private function assertMySqlCheckViolation(callable $operation): void
    {
        try {
            $operation();
            $this->fail('An enforced Task51 CHECK constraint must reject the invalid row.');
        } catch (DbException $exception) {
            $this->assertSame(3819, (int)($exception->errorInfo[1] ?? 0));
        }
    }

    private function assertMySqlAppendOnlyViolation(callable $operation): void
    {
        try {
            $operation();
            $this->fail('The Task51 transition ledger must reject UPDATE and DELETE.');
        } catch (DbException $exception) {
            $this->assertSame(1644, (int)($exception->errorInfo[1] ?? 0));
            $this->assertStringContainsString('append-only', $exception->getMessage());
        }
    }

    private function installFailureTrigger(int $ordinal): void
    {
        $this->db()->createCommand(
            'CREATE TRIGGER `' . $this->failureTrigger . '` '
                . 'BEFORE INSERT ON task51_stage_b_transition FOR EACH ROW '
                . "BEGIN IF NEW.ordinal = {$ordinal} THEN SIGNAL SQLSTATE '45000' "
                . "SET MESSAGE_TEXT = 'forced Task51 transition failure'; END IF; END"
        )->execute();
        $this->failureTriggerCreated = true;
    }

    private function dropFailureTrigger(): void
    {
        if (!$this->failureTriggerCreated) {
            return;
        }
        $this->db()->createCommand('DROP TRIGGER `' . $this->failureTrigger . '`')->execute();
        $this->failureTriggerCreated = false;
    }

    private function requireForkSupport(): void
    {
        if (!function_exists('pcntl_fork') || !function_exists('pcntl_waitpid')
            || !function_exists('posix_kill')) {
            $this->markTestSkipped('The real two-process CAS tests require the PHP pcntl extension.');
        }
    }

    /** @return array{gate: string, results: list<string>, ready: list<string>} */
    private function temporaryRaceFiles(int $count): array
    {
        $gate = tempnam(sys_get_temp_dir(), 'task51-gate-');
        if (!is_string($gate)) {
            throw new RuntimeException('Unable to allocate Task51 race gate.');
        }
        file_put_contents($gate, 'wait', LOCK_EX);
        $results = [];
        $ready = [];
        for ($index = 0; $index < $count; $index++) {
            $path = tempnam(sys_get_temp_dir(), 'task51-result-');
            if (!is_string($path)) {
                throw new RuntimeException('Unable to allocate Task51 race result.');
            }
            $results[] = $path;
            $readyPath = tempnam(sys_get_temp_dir(), 'task51-ready-');
            if (!is_string($readyPath)) {
                throw new RuntimeException('Unable to allocate Task51 race readiness marker.');
            }
            unlink($readyPath);
            $ready[] = $readyPath;
        }
        return ['gate' => $gate, 'results' => $results, 'ready' => $ready];
    }

    /** @param array{gate: string, results: list<string>, ready: list<string>} $paths */
    private function removeRaceFiles(array $paths): void
    {
        $all = array_merge([$paths['gate']], $paths['results'], $paths['ready']);
        foreach ($all as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function runClaimChild(
        string $gate,
        string $result,
        string $rawStageB,
        string $ready
    ): never {
        $db = $this->newConnection();
        file_put_contents($ready, json_encode([
            'connectionId' => (int)$db->createCommand('SELECT CONNECTION_ID()')->queryScalar(),
        ], JSON_THROW_ON_ERROR), LOCK_EX);
        while (trim((string)file_get_contents($gate)) !== 'go') {
            usleep(1000);
        }
        try {
            $receipt = $this->service($db)->claim($rawStageB, self::CAPABILITY);
            $payload = ['status' => 'success', 'receiptSha256' => hash('sha256', $receipt)];
        } catch (Task51CoordinatorException $exception) {
            $payload = ['status' => $exception->reason()];
        } catch (Throwable $exception) {
            $payload = [
                'status' => 'unexpected',
                'class' => $exception::class,
                'message' => $exception->getMessage(),
            ];
        } finally {
            $db->close();
        }
        file_put_contents($result, json_encode($payload, JSON_THROW_ON_ERROR), LOCK_EX);
        exit(0);
    }

    /**
     * @param list<array{operation: 'issue'|'consume', body: string, evidenceRef: ?string}> $operations
     * @return list<array<string, mixed>>
     */
    private function runConcurrentOperations(array $operations): array
    {
        $paths = $this->temporaryRaceFiles(count($operations));
        $children = [];
        $waited = [];
        $this->releaseSchemaLock();
        $this->db()->close();
        try {
            foreach ($operations as $index => $operation) {
                $pid = pcntl_fork();
                if ($pid === -1) {
                    throw new RuntimeException('Unable to fork Task51 coordinator contender.');
                }
                if ($pid === 0) {
                    $this->runCoordinatorChild(
                        $paths['gate'],
                        $paths['results'][$index],
                        $paths['ready'][$index],
                        $operation
                    );
                }
                $children[] = $pid;
            }
            $this->db()->open();
            $this->acquireSchemaLock();
            $this->waitForReady($paths['ready']);
            file_put_contents($paths['gate'], 'go', LOCK_EX);
            foreach ($children as $child) {
                $status = $this->waitForChild($child);
                $waited[$child] = true;
                $this->assertTrue(pcntl_wifexited($status));
                $this->assertSame(0, pcntl_wexitstatus($status));
            }
            return array_map(
                static fn(string $path): array => json_decode(
                    (string)file_get_contents($path),
                    true,
                    8,
                    JSON_THROW_ON_ERROR
                ),
                $paths['results']
            );
        } finally {
            file_put_contents($paths['gate'], 'go', LOCK_EX);
            foreach ($children as $child) {
                if (!isset($waited[$child])) {
                    try {
                        $this->waitForChild($child);
                    } catch (RuntimeException) {
                    }
                }
            }
            $this->removeRaceFiles($paths);
            $this->db()->open();
            if (!$this->schemaLockHeld) {
                $this->acquireSchemaLock();
            }
        }
    }

    /** @param array{operation: 'issue'|'consume', body: string, evidenceRef: ?string} $operation */
    private function runCoordinatorChild(string $gate, string $result, string $ready, array $operation): never
    {
        $db = $this->newConnection();
        file_put_contents($ready, json_encode([
            'connectionId' => (int)$db->createCommand('SELECT CONNECTION_ID()')->queryScalar(),
        ], JSON_THROW_ON_ERROR), LOCK_EX);
        while (trim((string)file_get_contents($gate)) !== 'go') {
            usleep(1000);
        }
        try {
            $service = $this->service($db);
            if ($operation['operation'] === 'issue') {
                $service->issue($operation['body'], self::CAPABILITY);
            } else {
                $service->consume($operation['body'], (string)$operation['evidenceRef']);
            }
            $payload = ['status' => 'success'];
        } catch (Task51CoordinatorException $exception) {
            $payload = ['status' => $exception->reason()];
        } catch (Throwable $exception) {
            $payload = [
                'status' => 'unexpected',
                'class' => $exception::class,
                'message' => $exception->getMessage(),
            ];
        } finally {
            $db->close();
        }
        file_put_contents($result, json_encode($payload, JSON_THROW_ON_ERROR), LOCK_EX);
        exit(0);
    }

    private function waitForChild(int $pid): int
    {
        $deadline = microtime(true) + 20.0;
        do {
            $waited = pcntl_waitpid($pid, $status, WNOHANG);
            if ($waited === $pid) {
                return $status;
            }
            if ($waited === -1) {
                throw new RuntimeException('Unable to wait for Task51 race child.');
            }
            usleep(10_000);
        } while (microtime(true) < $deadline);

        posix_kill($pid, SIGTERM);
        $termDeadline = microtime(true) + 1.0;
        do {
            $waited = pcntl_waitpid($pid, $status, WNOHANG);
            if ($waited === $pid) {
                throw new RuntimeException('Timed out waiting for Task51 race child; it was terminated.');
            }
            usleep(10_000);
        } while (microtime(true) < $termDeadline);
        posix_kill($pid, SIGKILL);
        pcntl_waitpid($pid, $status);
        throw new RuntimeException('Timed out waiting for Task51 race child; it was terminated.');
    }

    /** @param list<string> $paths @return list<array<string, mixed>> */
    private function waitForReady(array $paths): array
    {
        foreach ($paths as $path) {
            $this->waitForFile($path);
        }
        return array_map(
            static fn(string $path): array => json_decode(
                (string)file_get_contents($path),
                true,
                8,
                JSON_THROW_ON_ERROR
            ),
            $paths
        );
    }

    private function waitForRowLockWait(int $connectionId): void
    {
        $deadline = microtime(true) + 5.0;
        do {
            $waiting = (int)$this->db()->createCommand(
                'SELECT COUNT(*) FROM performance_schema.data_lock_waits AS w '
                    . 'INNER JOIN performance_schema.threads AS t '
                    . 'ON t.THREAD_ID = w.REQUESTING_THREAD_ID '
                    . 'WHERE t.PROCESSLIST_ID = :connectionId',
                [':connectionId' => $connectionId]
            )->queryScalar();
            if ($waiting > 0) {
                return;
            }
            usleep(10_000);
        } while (microtime(true) < $deadline);
        throw new RuntimeException('No observable MySQL row-lock wait was recorded for the contender.');
    }

    private function waitForFile(string $path): void
    {
        $deadline = microtime(true) + 5.0;
        while (!is_file($path)) {
            if (microtime(true) >= $deadline) {
                throw new RuntimeException('Timed out waiting for Task51 lock contender.');
            }
            usleep(1000);
        }
    }
}

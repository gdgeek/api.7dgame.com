<?php

namespace console\controllers;

use console\components\Task51AppliedSchemaFingerprint;
use RuntimeException;
use Yii;
use yii\console\controllers\MigrateController;
use yii\console\ExitCode;
use yii\db\Command;
use yii\db\Connection;

/** Exact-one migration surface for the Task 5.1 coordinator authority. */
final class Task51MigrateController extends MigrateController
{
    public const EXACT_MIGRATION = 'm260828_170000_create_task51_stage_b_coordinator_tables';

    public $db = 'task51CoordinatorDb';
    public $migrationPath = '@console/migrations';
    public $migrationTable = '{{%migration}}';

    private ?string $appliedSchemaSha256 = null;
    private bool $exactMigrationExecutedThisInvocation = false;

    public function beforeAction($action)
    {
        if (!in_array($action->id, ['plan', 'up'], true)) {
            throw new RuntimeException('Task 5.1 migration controller exposes only plan and exact-one up.');
        }
        if ($this->db !== 'task51CoordinatorDb'
            || $this->migrationPath !== '@console/migrations'
            || $this->migrationTable !== '{{%migration}}') {
            throw new RuntimeException('Task 5.1 migration authority/path override is forbidden.');
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
        $expectedPath = realpath(Yii::getAlias('@console/migrations'));
        $actualPath = is_string($this->migrationPath) ? realpath($this->migrationPath) : false;
        if ($expectedPath === false || $actualPath !== $expectedPath) {
            throw new RuntimeException('Task 5.1 migration path override is forbidden.');
        }
        $exactFile = $actualPath . DIRECTORY_SEPARATOR . self::EXACT_MIGRATION . '.php';
        if (!is_file($exactFile)) {
            throw new RuntimeException('Exact Task 5.1 migration file is unavailable.');
        }
        require_once $exactFile;
        if (!class_exists(self::EXACT_MIGRATION, false)) {
            throw new RuntimeException('Exact Task 5.1 migration class is unavailable.');
        }
        if (!$this->db instanceof Connection
            || get_class($this->db) !== Connection::class
            || $this->db->commandClass !== Command::class
            || $this->db->enableSlaves !== false
            || $this->db->tablePrefix !== '') {
            throw new RuntimeException('Task 5.1 migration requires exact task51CoordinatorDb.');
        }
        Task51AppliedSchemaFingerprint::assertSupportedDatabase($this->db);

        return true;
    }

    public function actionPlan(): int
    {
        $pending = $this->assertExactPendingPlan();
        if ($pending === []) {
            $this->stdout(
                'TASK51_MIGRATION_SCHEMA_SHA256=' . $this->requireAppliedSchemaSha256() . PHP_EOL
            );
            $this->stdout('TASK51_MIGRATION_PLAN=ALREADY_APPLIED' . PHP_EOL);
        } else {
            $this->stdout('TASK51_MIGRATION_PLAN=EXACT_ONE:' . self::EXACT_MIGRATION . PHP_EOL);
        }

        return ExitCode::OK;
    }

    public function actionUp($limit = 1)
    {
        if ((string)$limit !== '1') {
            throw new RuntimeException('Task 5.1 migration limit must be exactly one.');
        }
        $pending = $this->assertExactPendingPlan();
        $this->exactMigrationExecutedThisInvocation = false;
        $result = parent::actionUp(1);
        if ($result !== ExitCode::OK) {
            return $result;
        }
        if (($pending !== []) !== $this->exactMigrationExecutedThisInvocation) {
            throw new RuntimeException(
                'Task 5.1 migration ownership changed concurrently; refusing a successful up result.'
            );
        }
        if ($this->getNewMigrations() !== []) {
            throw new RuntimeException(
                'Task 5.1 exact migration was not applied; refusing a successful up result.'
            );
        }
        $this->appliedSchemaSha256 = (new Task51AppliedSchemaFingerprint())
            ->assertAlreadyApplied($this->db, $this->migrationTable, self::EXACT_MIGRATION);
        $this->stdout(
            'TASK51_MIGRATION_APPLY=' . ($this->exactMigrationExecutedThisInvocation
                ? 'APPLIED_EXACT_ONE'
                : 'ALREADY_APPLIED_NOOP') . PHP_EOL
        );
        $this->stdout(
            'TASK51_MIGRATION_SCHEMA_SHA256=' . $this->requireAppliedSchemaSha256() . PHP_EOL
        );

        return $result;
    }

    /** Record execution by this process instead of inferring it from a stale plan. */
    protected function migrateUp($class)
    {
        if (!is_string($class)
            || !hash_equals(self::EXACT_MIGRATION, $class)
            || $this->exactMigrationExecutedThisInvocation) {
            throw new RuntimeException('Task 5.1 migration execution is not exact-one.');
        }
        $result = parent::migrateUp($class);
        if ($result) {
            $this->exactMigrationExecutedThisInvocation = true;
        }
        return $result;
    }

    /** @return list<string> */
    protected function getNewMigrations()
    {
        return array_values(array_filter(
            parent::getNewMigrations(),
            static fn(string $migration): bool => hash_equals(self::EXACT_MIGRATION, $migration)
        ));
    }

    /** @return list<string> */
    private function assertExactPendingPlan(): array
    {
        (new Task51AppliedSchemaFingerprint())->assertMigrationHistoryTable(
            $this->db,
            $this->migrationTable
        );
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            throw new RuntimeException('Migration history authority must already exist.');
        }
        $pending = $this->getNewMigrations();
        if ($pending !== [] && $pending !== [self::EXACT_MIGRATION]) {
            throw new RuntimeException('Task 5.1 migration plan is not exact-one.');
        }
        $executionExists = $this->db->schema
            ->getTableSchema(Task51AppliedSchemaFingerprint::EXECUTION_TABLE, true) !== null;
        $transitionExists = $this->db->schema
            ->getTableSchema(Task51AppliedSchemaFingerprint::TRANSITION_TABLE, true) !== null;
        if (($pending === [] && (!$executionExists || !$transitionExists))
            || ($pending !== [] && ($executionExists || $transitionExists))) {
            throw new RuntimeException('Task 5.1 migration history/schema authority is inconsistent.');
        }
        if ($pending === []) {
            $this->appliedSchemaSha256 = (new Task51AppliedSchemaFingerprint())
                ->assertAlreadyApplied($this->db, $this->migrationTable, self::EXACT_MIGRATION);
        }

        return $pending;
    }

    private function requireAppliedSchemaSha256(): string
    {
        if (!is_string($this->appliedSchemaSha256)
            || preg_match('/\A[0-9a-f]{64}\z/D', $this->appliedSchemaSha256) !== 1) {
            throw new RuntimeException('Task 5.1 applied-schema fingerprint is unavailable.');
        }
        return $this->appliedSchemaSha256;
    }
}

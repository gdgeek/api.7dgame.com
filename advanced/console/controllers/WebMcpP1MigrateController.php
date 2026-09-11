<?php

namespace console\controllers;

use RuntimeException;
use Yii;
use yii\console\controllers\MigrateController;
use yii\console\ExitCode;
use yii\db\Query;

/** Apply only the additive P1 schema, without running unrelated pending migrations. */
final class WebMcpP1MigrateController extends MigrateController
{
    public const EXACT_MIGRATION = 'm260911_230000_add_webmcp_write_receipts';
    public $migrationPath = '@console/migrations';

    public function beforeAction($action)
    {
        if (!in_array($action->id, ['plan', 'up'], true)
            || $this->db !== 'db'
            || $this->migrationPath !== '@console/migrations'
            || $this->migrationTable !== '{{%migration}}'
            || $this->migrationNamespaces !== []) {
            throw new RuntimeException('WebMCP P1 exposes only plan/up against the application database and exact migration.');
        }
        if (!parent::beforeAction($action)) {
            return false;
        }
        require_once dirname(__DIR__) . '/migrations/' . self::EXACT_MIGRATION . '.php';
        return true;
    }

    public function actionPlan(): int
    {
        $pending = $this->getNewMigrations();
        if ($pending === []) {
            $this->assertSchema();
        }
        $this->stdout('WEBMCP_P1_MIGRATION=' . ($pending === [] ? 'ALREADY_APPLIED' : self::EXACT_MIGRATION) . PHP_EOL);
        return ExitCode::OK;
    }

    public function actionUp($limit = 1)
    {
        if ((string) $limit !== '1') {
            throw new RuntimeException('WebMCP P1 migration limit must be exactly one.');
        }
        $this->getMigrationHistory(1); // Create history only in up; plan stays read-only.
        $result = parent::actionUp(1);
        if ($result === ExitCode::OK) {
            if ($this->getNewMigrations() !== []) {
                throw new RuntimeException('WebMCP P1 migration was not applied.');
            }
            $this->assertSchema();
            $this->stdout('WEBMCP_P1_SCHEMA=READY' . PHP_EOL);
        }
        return $result;
    }

    protected function getNewMigrations()
    {
        $applied = $this->db->getTableSchema($this->migrationTable, true) !== null
            && (new Query())->from($this->migrationTable)->where(['version' => self::EXACT_MIGRATION])->exists($this->db);
        return $applied ? [] : [self::EXACT_MIGRATION];
    }

    protected function migrateUp($class)
    {
        if ($class !== self::EXACT_MIGRATION) {
            throw new RuntimeException('Unexpected migration class.');
        }
        return parent::migrateUp($class);
    }

    private function assertSchema(): void
    {
        $class = self::EXACT_MIGRATION;
        (new $class(['db' => $this->db]))->assertSchema();
    }
}

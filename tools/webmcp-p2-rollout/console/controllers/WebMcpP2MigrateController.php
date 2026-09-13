<?php

namespace console\controllers;

use RuntimeException;
use Yii;
use yii\console\controllers\MigrateController;
use yii\console\ExitCode;
use yii\db\Query;

/** Apply only the additive P2 schema, without running unrelated pending migrations. */
final class WebMcpP2MigrateController extends MigrateController
{
    public const EXACT_MIGRATION = 'm260913_120000_add_scene_publication_history';
    public $migrationPath = '@console/migrations';

    public function beforeAction($action)
    {
        if (!in_array($action->id, ['plan', 'up', 'verify'], true)
            || $this->db !== 'db'
            || $this->migrationPath !== '@console/migrations'
            || $this->migrationTable !== '{{%migration}}'
            || $this->migrationNamespaces !== []) {
            throw new RuntimeException('WebMCP P2 exposes only plan/up/verify against the application database and exact migration.');
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
        $this->stdout('WEBMCP_P2_MIGRATION=' . ($pending === [] ? 'ALREADY_APPLIED' : self::EXACT_MIGRATION) . PHP_EOL);
        return ExitCode::OK;
    }

    public function actionVerify(): int
    {
        if ($this->getNewMigrations() !== []) throw new RuntimeException('P2 migration is not applied');
        $this->assertSchema();
        \api\modules\v1\services\PublicationArchive::assertTransactionalStorage();
        foreach (['publications', 'publication-version'] as $action) {
            $route = '@restful/v1/verse/' . $action;
            if (!(new Query())->from('{{%auth_item}}')->where(['name' => $route, 'type' => 2])->exists($this->db)) {
                throw new RuntimeException('Missing P2 RBAC route');
            }
            foreach (['view', 'update'] as $parent) {
                $name = '@restful/v1/verse/' . $parent;
                if ((new Query())->from('{{%auth_item}}')->where(['name' => $name])->exists($this->db)
                    && !(new Query())->from('{{%auth_item_child}}')->where(['parent' => $name, 'child' => $route])->exists($this->db)) {
                    throw new RuntimeException('Missing P2 RBAC inheritance');
                }
            }
        }
        $this->stdout('WEBMCP_P2_STORAGE_AND_RBAC=READY' . PHP_EOL);
        return ExitCode::OK;
    }

    public function actionUp($limit = 1)
    {
        if ((string) $limit !== '1') {
            throw new RuntimeException('WebMCP P2 migration limit must be exactly one.');
        }
        $this->getMigrationHistory(1); // Create history only in up; plan stays read-only.
        $result = parent::actionUp(1);
        if ($result === ExitCode::OK) {
            if ($this->getNewMigrations() !== []) {
                throw new RuntimeException('WebMCP P2 migration was not applied.');
            }
            $this->assertSchema();
            $this->stdout('WEBMCP_P2_SCHEMA=READY' . PHP_EOL);
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

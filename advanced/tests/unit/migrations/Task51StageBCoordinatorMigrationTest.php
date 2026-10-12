<?php

namespace tests\unit\migrations;

use common\components\CynosDbConnection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class Task51StageBCoordinatorMigrationTest extends TestCase
{
    public function testMigrationDeclaresSingleInnoDbAuthorityAndAppendOnlyLedger(): void
    {
        $path = dirname(__DIR__, 3)
            . '/console/migrations/m260828_170000_create_task51_stage_b_coordinator_tables.php';
        $source = file_get_contents($path);
        $helperSource = file_get_contents(
            dirname(__DIR__, 3) . '/console/components/Task51AppliedSchemaFingerprint.php'
        );
        $this->assertIsString($source);
        $this->assertIsString($helperSource);
        $guardPosition = strpos($source, '$this->assertSupportedDatabase();');
        $firstDdlPosition = strpos($source, '$this->createTable(');
        $this->assertIsInt($guardPosition);
        $this->assertIsInt($firstDdlPosition);
        $this->assertLessThan($firstDdlPosition, $guardPosition);
        $this->assertStringContainsString(
            'Task51AppliedSchemaFingerprint::assertSupportedDatabase($this->db)',
            $source
        );
        foreach ([
            "driverName !== 'mysql'",
            'get_class($db) !== Connection::class',
            '$db->commandClass !== Command::class',
            '$db->enableSlaves !== false',
            "version_compare(\$matches[1], '8.0.19', '<')",
            "stripos(\$serverVersion, 'mariadb')",
            'SELECT @@version_comment',
            "stripos(\$versionComment, 'percona')",
            'SET SESSION lock_wait_timeout = ',
            'SET SESSION innodb_lock_wait_timeout = ',
        ] as $sharedGuard) {
            $this->assertStringContainsString($sharedGuard, $helperSource);
        }
        $this->assertStringContainsString('ENGINE=InnoDB', $source);
        $this->assertStringContainsString("{{%task51_stage_b_execution}}", $source);
        $this->assertStringContainsString("{{%task51_stage_b_transition}}", $source);
        $this->assertStringContainsString('uq_task51_stage_b_approval_ref', $source);
        $this->assertStringContainsString('uq_task51_stage_b_sha256', $source);
        $this->assertStringContainsString("'state_version'", $source);
        $this->assertStringContainsString("'consumption_count'", $source);
        $this->assertStringContainsString("'consumption_receipt_canonical'", $source);
        $this->assertStringContainsString("'consumption_receipt_sha256'", $source);
        $this->assertStringContainsString(
            "'production_direct_matrix_evidence_ref'",
            $source
        );
        $this->assertStringContainsString(
            "'production_direct_matrix_subject_digest'",
            $source
        );
        $this->assertStringContainsString('ck_task51_stage_b_time_order', $source);
        $this->assertStringContainsString('[[runner_export_receipt_ref]] IS NOT NULL', $source);
        $this->assertStringContainsString('[[claim_receipt_sha256]] IS NULL', $source);
        $this->assertStringContainsString('[[consumed_at]] >= [[claimed_at]]', $source);
        $this->assertStringContainsString('uq_task51_stage_b_transition_ordinal', $source);
        $this->assertStringContainsString('ck_task51_stage_b_transition_shape', $source);
        $this->assertStringContainsString('[[from_state]] IS NOT NULL', $source);
        $this->assertStringContainsString('ck_task51_stage_b_transition_evidence_sha256', $source);
        $this->assertStringContainsString("[[evidence_sha256]] REGEXP '^[0-9a-f]{64}$'", $source);
        $this->assertStringContainsString('trg_task51_stage_b_transition_no_update', $source);
        $this->assertStringContainsString('trg_task51_stage_b_transition_no_delete', $source);
        $this->assertStringContainsString("SIGNAL SQLSTATE '45000'", $source);
        $this->assertStringContainsString('public function safeDown(): bool', $source);
        $this->assertStringContainsString('return false;', $source);
        $this->assertStringNotContainsString('$this->dropTable(', $source);
        $this->assertStringNotContainsString('$this->dropForeignKey(', $source);
        $this->assertStringNotContainsString('$this->dropCheck(', $source);
        $this->assertStringNotContainsString('updateTable', $source);
    }

    public function testMigrationRejectsCynosAdapterBeforeFirstDdl(): void
    {
        require_once dirname(__DIR__, 3)
            . '/console/migrations/m260828_170000_create_task51_stage_b_coordinator_tables.php';
        $migration = new \m260828_170000_create_task51_stage_b_coordinator_tables([
            'db' => new CynosDbConnection([
                'dsn' => 'mysql:host=invalid.example;dbname=task51_test_contract',
                'commandClass' => \common\components\CynosDbCommand::class,
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('exact standard task51CoordinatorDb');
        $migration->safeUp();
    }

    public function testConsolePublishesExactNonRetryingMigrationAuthority(): void
    {
        $config = require dirname(__DIR__, 4) . '/files/console/config/main.php';
        $coordinatorDb = $config['components']['task51CoordinatorDb'] ?? null;
        $controller = $config['controllerMap']['task51-migrate'] ?? null;

        $this->assertIsArray($coordinatorDb);
        $this->assertSame(\yii\db\Connection::class, $coordinatorDb['class']);
        $this->assertSame(\yii\db\Command::class, $coordinatorDb['commandClass']);
        $this->assertFalse($coordinatorDb['enableSlaves']);
        $this->assertSame(5, $coordinatorDb['attributes'][\PDO::ATTR_TIMEOUT] ?? null);
        $this->assertNotSame(\common\components\CynosDbConnection::class, $coordinatorDb['class']);
        $this->assertIsArray($controller);
        $this->assertSame(\console\controllers\Task51MigrateController::class, $controller['class']);
        $this->assertSame('task51CoordinatorDb', $controller['db']);
        $this->assertSame('@console/migrations', $controller['migrationPath']);
        $this->assertSame('{{%migration}}', $controller['migrationTable']);
    }

    public function testDedicatedCommandMachineGatesOneExactMigration(): void
    {
        $root = dirname(__DIR__, 4);
        $source = file_get_contents($root . '/advanced/console/controllers/Task51MigrateController.php');
        $runbook = file_get_contents($root . '/docs/task51-stage-b-coordinator.md');

        $this->assertIsString($source);
        $this->assertIsString($runbook);
        $this->assertStringContainsString(
            "EXACT_MIGRATION = 'm260828_170000_create_task51_stage_b_coordinator_tables'",
            $source
        );
        $this->assertStringContainsString("in_array(\$action->id, ['plan', 'up'], true)", $source);
        $this->assertStringContainsString("(string)\$limit !== '1'", $source);
        $this->assertStringContainsString("\$this->db !== 'task51CoordinatorDb'", $source);
        $this->assertStringContainsString("\$this->db->tablePrefix !== ''", $source);
        $this->assertStringContainsString('hash_equals(self::EXACT_MIGRATION, $migration)', $source);
        $this->assertStringContainsString('migration history/schema authority is inconsistent', $source);
        $this->assertStringContainsString('protected function migrateUp($class)', $source);
        $this->assertStringContainsString('migration ownership changed concurrently', $source);
        $this->assertStringContainsString('assertMigrationHistoryTable(', $source);
        $adapterGuardPosition = strpos(
            $source,
            'Task51AppliedSchemaFingerprint::assertSupportedDatabase($this->db);'
        );
        $schemaQueryPosition = strpos($source, 'private function assertExactPendingPlan');
        $this->assertIsInt($adapterGuardPosition);
        $this->assertIsInt($schemaQueryPosition);
        $this->assertLessThan($schemaQueryPosition, $adapterGuardPosition);
        $this->assertStringContainsString('TASK51_MIGRATION_PLAN=EXACT_ONE:', $source);
        $this->assertStringContainsString('TASK51_MIGRATION_APPLY=', $source);
        $this->assertStringContainsString(
            'php yii task51-migrate/plan --db=task51CoordinatorDb',
            $runbook
        );
        $this->assertStringContainsString(
            'php yii task51-migrate/up 1 --db=task51CoordinatorDb --interactive=0',
            $runbook
        );
        $this->assertStringNotContainsString('php yii migrate --db=task51CoordinatorDb', $runbook);
    }

    public function testOptInMySqlSuiteCannotFallBackToApplicationDatabase(): void
    {
        $path = dirname(__DIR__, 2) . '/integration/Task51StageBMySqlCasTest.php';
        $source = file_get_contents($path);
        $this->assertIsString($source);
        $this->assertStringContainsString("getenv('TASK51_MYSQL_INTEGRATION') !== '1'", $source);
        $this->assertStringContainsString("getenv('TASK51_MYSQL_TEST_DSN')", $source);
        $this->assertStringContainsString("getenv('TASK51_MYSQL_TEST_USER')", $source);
        $this->assertStringContainsString("getenv('TASK51_MYSQL_TEST_PASSWORD')", $source);
        $this->assertStringContainsString("/^task51_test_[a-z0-9_]+$/D", $source);
        $this->assertStringContainsString('Refusing to overwrite existing', $source);
        $this->assertStringContainsString('Task51ArtifactFixture::stageB', $source);
        $this->assertStringContainsString('Task51ArtifactFixture::runnerExport', $source);
        $this->assertStringContainsString('waitForDatabaseTimeAfter($exportedAt)', $source);
        $this->assertStringContainsString('SELECT GET_LOCK(:lockName, 30)', $source);
        $this->assertStringContainsString('performance_schema.data_lock_waits', $source);
        $this->assertStringContainsString('SET SESSION lock_wait_timeout = 20', $source);
        $this->assertStringContainsString("'attributes' => [\\PDO::ATTR_TIMEOUT => 5]", $source);
        $this->assertStringContainsString('assertMySqlAppendOnlyViolation', $source);
        $this->assertStringContainsString("DROP TABLE IF EXISTS `' . self::TRANSITION_TABLE", $source);
        $this->assertStringContainsString("DROP TABLE IF EXISTS `' . self::EXECUTION_TABLE", $source);
        $this->assertStringNotContainsString('Yii::$app->db', $source);
        $this->assertStringNotContainsString("getenv('MYSQL_DB')", $source);
    }

    public function testCiExecutesExactMigrationBeforeGenericMigrateAndRealMySqlSuite(): void
    {
        $workflow = file_get_contents(
            dirname(__DIR__, 4) . '/.github/workflows/ci.yml'
        );
        $this->assertIsString($workflow);
        $this->assertStringContainsString(
            'extensions: pdo, pdo_mysql, gd, zip, pcntl, posix',
            $workflow
        );
        foreach ([
            "'task51-migrate' => [",
            "'db' => 'task51CoordinatorDb'",
            "'task51CoordinatorDb' => [",
            "'class' => 'yii\\db\\Connection'",
            "'commandClass' => 'yii\\db\\Command'",
            "'enableSlaves' => false",
            "'attributes' => [\\PDO::ATTR_TIMEOUT => 5]",
        ] as $expectedConfig) {
            $this->assertStringContainsString($expectedConfig, $workflow);
        }

        $rbacPosition = strpos(
            $workflow,
            'php yii migrate/up --migrationPath=@yii/rbac/migrations --interactive=0'
        );
        $planPosition = strpos(
            $workflow,
            'php yii task51-migrate/plan --db=task51CoordinatorDb'
        );
        $applyPosition = strpos(
            $workflow,
            'php yii task51-migrate/up 1 --db=task51CoordinatorDb --interactive=0'
        );
        $genericPosition = strpos($workflow, 'php yii migrate --interactive=0');
        $this->assertIsInt($rbacPosition);
        $this->assertIsInt($planPosition);
        $this->assertIsInt($applyPosition);
        $this->assertIsInt($genericPosition);
        $this->assertLessThan($planPosition, $rbacPosition);
        $this->assertLessThan($applyPosition, $planPosition);
        $this->assertLessThan($genericPosition, $applyPosition);

        foreach ([
            'CREATE DATABASE task51_test_ci',
            "TASK51_MYSQL_INTEGRATION: '1'",
            'TASK51_MYSQL_TEST_DSN: mysql:host=127.0.0.1;dbname=task51_test_ci',
            'TASK51_MYSQL_TEST_USER: root',
            'TASK51_MYSQL_TEST_PASSWORD: root',
            'tests/integration/Task51StageBMySqlCasTest.php',
            '--testdox --fail-on-skipped',
            'grep -Fxc',
            "grep -c '^TASK51_MIGRATION_PLAN='",
            'TASK51_MIGRATION_PLAN=EXACT_ONE:m260828_170000_create_task51_stage_b_coordinator_tables',
            'TASK51_MIGRATION_PLAN=ALREADY_APPLIED',
            'TASK51_MIGRATION_APPLY=APPLIED_EXACT_ONE',
            'TASK51_MIGRATION_APPLY=ALREADY_APPLIED_NOOP',
            "grep -c '^TASK51_MIGRATION_APPLY='",
            'release_noop_output=',
        ] as $expectedIntegrationGate) {
            $this->assertStringContainsString($expectedIntegrationGate, $workflow);
        }

        $this->assertSame(
            2,
            substr_count($workflow, 'php yii task51-migrate/plan --db=task51CoordinatorDb')
        );
        $this->assertSame(
            1,
            substr_count(
                $workflow,
                'php yii task51-migrate/up 1 --db=task51CoordinatorDb --interactive=0'
            )
        );

        $smokeStepPosition = strpos($workflow, '- name: Smoke Task 5.1 Release Image');
        $pushStepPosition = strpos($workflow, '- name: Push Docker Image');
        $firstDockerPushPosition = strpos($workflow, 'docker push');
        $this->assertIsInt($smokeStepPosition);
        $this->assertIsInt($pushStepPosition);
        $this->assertIsInt($firstDockerPushPosition);
        $this->assertGreaterThan($smokeStepPosition, $pushStepPosition);
        $this->assertGreaterThan($pushStepPosition, $firstDockerPushPosition);
        $buildJob = substr(
            $workflow,
            strpos($workflow, "\n  build:\n") ?: 0,
            (strpos($workflow, "\n  publish_notice:\n") ?: strlen($workflow))
                - (strpos($workflow, "\n  build:\n") ?: 0)
        );
        $this->assertStringNotContainsString('continue-on-error:', $buildJob);
        $this->assertStringContainsString('queue: max', $workflow);
    }

    public function testCiSmokesTheExactBuiltReleaseImageBeforeAnyPush(): void
    {
        $workflow = file_get_contents(
            dirname(__DIR__, 4) . '/.github/workflows/ci.yml'
        );
        $this->assertIsString($workflow);

        $buildPosition = strpos($workflow, '- name: Build Docker Image');
        $smokePosition = strpos($workflow, '- name: Smoke Task 5.1 Release Image');
        $pushPosition = strpos($workflow, '- name: Push Docker Image');
        $this->assertIsInt($buildPosition);
        $this->assertIsInt($smokePosition);
        $this->assertIsInt($pushPosition);
        $this->assertLessThan($smokePosition, $buildPosition);
        $this->assertLessThan($pushPosition, $smokePosition);
        $this->assertStringNotContainsString('- name: Build and Push Docker Image', $workflow);

        foreach ([
            'group: server-ci-${{ github.workflow }}-${{ github.ref }}',
            'cancel-in-progress: false',
            '--label "org.opencontainers.image.revision=${{ github.sha }}"',
            'TASK51_RELEASE_IMMUTABLE_IMAGE=',
            '${{ github.sha }}-${{ github.run_id }}-${{ github.run_attempt }}',
            'git ls-remote --exit-code origin "${GITHUB_REF}"',
            '/var/www/html/advanced/GIT_COMMIT',
            'docker image inspect',
            'org.opencontainers.image.revision',
            'test "${actual_oci_revision}" = "${expected_commit}"',
            '/var/www/html/advanced/console/config/main.php',
            'CREATE DATABASE task51_release_image_ci',
            '--network host',
            'php ./yii task51-migrate/plan --db=task51CoordinatorDb',
            'php ./yii task51-migrate/up 1',
            'TASK51_MIGRATION_SCHEMA_SHA256=',
            'release_migration_row_count',
            'release_table_count',
            '/var/www/html/advanced/api/web/index.php',
            'php_ini_loaded_file()',
            'php_ini_scanned_files()',
            'zz-task51-security.ini',
            'task51_random_base64url()',
            'task51_assert_container_files_clean()',
            '/var/www/html/advanced/api/runtime /var/log/apache2',
            'grep -rFq -- "${needle}"',
            'openssl rand -base64 32',
            'test "${#trace_sentinel}" = 43',
            'grep -Fq -- "${trace_sentinel}"',
            'apache2ctl -t',
            'apache2ctl -S',
            "default server api\\.xrteeth\\.com",
            '-e YII_DEBUG=1',
            '-e YII_ENV=dev',
            "test \"\${status}\" = '404'",
            'test ! -s "${smoke_dir}/debug-body"',
            'no-store, private, max-age=0',
            'test "${#http_sentinel}" = 43',
            'grep -Fq -- "${http_sentinel}"',
            '-e YII_DEBUG=0',
            '-e YII_ENV=prod',
            '-e TASK51_STAGE_B_COORDINATOR_ENABLED=0',
            '-e MYSQL_HOST=127.0.0.1',
            '-e REDIS_PORT=1',
            '/v1/task51/stage-b/claim',
            'test "${#prod_http_sentinel}" = 43',
            'grep -Fq -- "${prod_http_sentinel}"',
            'test -s "${smoke_dir}/prod-body"',
            'JSON_THROW_ON_ERROR',
            '($body["status"] ?? null) !== 404',
            'application/json',
            'pragma:[[:space:]]*no-cache',
            'expires:[[:space:]]*0',
            'x-content-type-options:[[:space:]]*nosniff',
            'head -c 16385 /dev/zero',
            '/index.php/v1/task51/stage-b/claim',
            'test "${oversize_status}" = \'413\'',
            'docker logs "${container_id}" > "${smoke_dir}/prod-container.log"',
            'docker push "${TASK51_RELEASE_IMMUTABLE_IMAGE}"',
            'docker push "${TASK51_RELEASE_IMAGE}"',
            'docker buildx imagetools inspect --raw',
            'test "${branch_manifest_sha}" = "${immutable_manifest_sha}"',
            'test "${latest_manifest_sha}" = "${immutable_manifest_sha}"',
            'docker pull "${TASK51_RELEASE_IMMUTABLE_IMAGE}"',
            'test "${remote_oci_revision}" = "${expected_commit}"',
        ] as $expectedImageGate) {
            $this->assertStringContainsString($expectedImageGate, $workflow);
        }
    }
}

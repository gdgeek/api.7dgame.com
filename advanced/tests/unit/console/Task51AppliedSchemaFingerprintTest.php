<?php

namespace tests\unit\console;

use console\components\Task51AppliedSchemaFingerprint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class Task51AppliedSchemaFingerprintTest extends TestCase
{
    public function testRestrictedCheckParserNormalizesMySqlPresentationOnly(): void
    {
        $expected = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "state IN ('ISSUED', 'CLAIMED', 'CONSUMED')"
        );
        $actual = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "((`state` in (_utf8mb4'ISSUED',_ascii'CLAIMED',_utf8mb3'CONSUMED')))"
        );

        $this->assertSame($expected, $actual);
    }

    public function testRestrictedCheckParserNormalizesMySqlEscapedIntroducedDelimitersOnly(): void
    {
        $expected = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "state IN ('ISSUED', 'CLAIMED', 'CONSUMED')"
        );
        $actual = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "`state` in (_ascii\\'ISSUED\\',_utf8mb4\\'CLAIMED\\',_utf8mb3\\'CONSUMED\\')"
        );

        $this->assertSame($expected, $actual);
    }

    public function testRestrictedCheckParserNormalizesRegexpLikeRewrite(): void
    {
        $infix = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "evidence_sha256 REGEXP '^[0-9a-f]{64}$'"
        );
        $function = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "regexp_like(`evidence_sha256`,_utf8mb4'^[0-9a-f]{64}$')"
        );

        $this->assertSame($infix, $function);
    }

    public function testRestrictedCheckParserPreservesSecurityRelevantTokens(): void
    {
        $required = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "claimed_at IS NOT NULL AND consumed_at >= claimed_at"
        );
        $weakened = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "claimed_at IS NULL AND consumed_at >= claimed_at"
        );

        $this->assertNotSame($required, $weakened);
    }

    public function testRestrictedCheckParserFlattensPresentationOnlyAssociativity(): void
    {
        $expected = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "state = 'CLAIMED' AND state_version = 1 AND claim_count = 1"
        );
        $actual = Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression(
            "((`state` = 'CLAIMED' AND `state_version` = 1) AND `claim_count` = 1)"
        );

        $this->assertSame($expected, $actual);
    }

    #[DataProvider('forbiddenCheckProvider')]
    public function testRestrictedCheckParserRejectsGrammarExpansion(string $expression): void
    {
        $this->expectException(RuntimeException::class);
        Task51AppliedSchemaFingerprint::canonicalizeRestrictedExpression($expression);
    }

    /** @return iterable<string,array{string}> */
    public static function forbiddenCheckProvider(): iterable
    {
        yield 'comment' => ["state = 'ISSUED' /* bypass */"];
        yield 'second statement' => ["state = 'ISSUED'; SELECT 1"];
        yield 'unknown function' => ["LOWER(state) = 'issued'"];
        yield 'weaker operator outside grammar' => ['state_version <= 2'];
        yield 'backslash escape' => ["evidence_sha256 REGEXP '^\\w+$'"];
        yield 'escaped delimiter without introduced charset' => ["state = \\'ISSUED\\'"];
        yield 'unapproved introduced charset' => ["state = _latin1\\'ISSUED\\'"];
        yield 'backslash inside escaped-delimiter literal' => [
            "state = _ascii\\'ISS\\\\UED\\'",
        ];
        yield 'unterminated escaped-delimiter literal' => ["state = _ascii\\'ISSUED"];
        yield 'qualified identifier' => ["task51_stage_b_execution.state = 'ISSUED'"];
    }

    public function testRestrictedSignalParserNormalizesOptionalValueAndWhitespace(): void
    {
        $expected = Task51AppliedSchemaFingerprint::canonicalizeTriggerAction(
            "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Task 5.1 transition ledger is append-only'"
        );
        $actual = Task51AppliedSchemaFingerprint::canonicalizeTriggerAction(
            "signal sqlstate value '45000'\n set message_text='Task 5.1 transition ledger is append-only'"
        );

        $this->assertSame($expected, $actual);
    }

    public function testRestrictedSignalParserRejectsTrailingAction(): void
    {
        $this->expectException(RuntimeException::class);
        Task51AppliedSchemaFingerprint::canonicalizeTriggerAction(
            "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'blocked'; SET @x = 1"
        );
    }

    public function testImplementationIsInformationSchemaExactSetAndMdlBased(): void
    {
        $root = dirname(__DIR__, 3);
        $helper = file_get_contents(
            $root . '/console/components/Task51AppliedSchemaFingerprint.php'
        );
        $controller = file_get_contents($root . '/console/controllers/Task51MigrateController.php');
        $migration = file_get_contents(
            $root . '/console/migrations/m260828_170000_create_task51_stage_b_coordinator_tables.php'
        );

        $this->assertIsString($helper);
        $this->assertIsString($controller);
        $this->assertIsString($migration);
        foreach ([
            'information_schema.TABLES',
            'information_schema.COLUMNS',
            'CHARACTER_OCTET_LENGTH AS character_octet_length',
            'information_schema.STATISTICS',
            'information_schema.TABLE_CONSTRAINTS',
            'information_schema.CHECK_CONSTRAINTS',
            'information_schema.REFERENTIAL_CONSTRAINTS',
            'information_schema.KEY_COLUMN_USAGE',
            'information_schema.TRIGGERS',
            'information_schema.PARTITIONS',
            'FOR SHARE',
            'Transaction::REPEATABLE_READ',
            '$db->getTransaction() !== null',
            'private const LOCK_WAIT_SECONDS = 20;',
            "'SELECT 1 FROM '",
            "CAST(TABLE_NAME AS BINARY) = CAST(:migration AS BINARY)",
            "'BASE TABLE'",
            "'InnoDB'",
            "'SET SESSION lock_wait_timeout = '",
            "'SET SESSION innodb_lock_wait_timeout = '",
            'hash_equals($expectedJson, $actualJson)',
            "preg_match('/\\A[a-z0-9_]*\\z/D', \$prefix)",
            "'characterOctetLength' => \$characterLength",
            "'blob',\n                false,\n                65535",
        ] as $required) {
            $this->assertStringContainsString($required, $helper);
        }
        $this->assertStringNotContainsString('SHOW CREATE', substr(
            $helper,
            strpos($helper, 'final class Task51AppliedSchemaFingerprint') ?: 0
        ));
        $this->assertStringContainsString(
            '->assertAlreadyApplied($this->db, $this->migrationTable, self::EXACT_MIGRATION)',
            $controller
        );
        $this->assertStringContainsString(
            'TASK51_MIGRATION_SCHEMA_SHA256=',
            $controller
        );
        $this->assertStringContainsString('TASK51_MIGRATION_APPLY=', $controller);
        $this->assertStringContainsString('APPLIED_EXACT_ONE', $controller);
        $this->assertStringContainsString('ALREADY_APPLIED_NOOP', $controller);
        $this->assertStringContainsString(
            'migration ownership changed concurrently',
            $controller
        );
        $this->assertStringContainsString('protected function migrateUp($class)', $controller);
        $this->assertStringContainsString(
            '$this->exactMigrationExecutedThisInvocation = true',
            $controller
        );
        $this->assertStringContainsString(
            'Task51AppliedSchemaFingerprint::assertSupportedDatabase($this->db)',
            $migration
        );
        $this->assertStringContainsString("\$this->db->tablePrefix !== ''", $migration);
        $this->assertStringContainsString("\$this->db->tablePrefix !== ''", $controller);
        $this->assertStringContainsString(
            'assertMigrationHistoryTable(',
            $controller
        );
    }

    public function testManifestPinsEveryCriticalObjectName(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 3) . '/console/components/Task51AppliedSchemaFingerprint.php'
        );
        $this->assertIsString($source);

        foreach ([
            'uq_task51_stage_b_approval_ref',
            'uq_task51_stage_b_sha256',
            'uq_task51_stage_b_claim_capability_sha256',
            'idx_task51_stage_b_state_expiry',
            'idx_task51_stage_b_runner_export_sha256',
            'uq_task51_stage_b_transition_ordinal',
            'idx_task51_stage_b_transition_time',
            'ck_task51_stage_b_state',
            'ck_task51_stage_b_counts',
            'ck_task51_stage_b_receipts',
            'ck_task51_stage_b_time_order',
            'ck_task51_stage_b_transition_shape',
            'ck_task51_stage_b_transition_evidence_sha256',
            'fk_task51_stage_b_transition_execution',
            'trg_task51_stage_b_transition_no_update',
            'trg_task51_stage_b_transition_no_delete',
        ] as $name) {
            $this->assertStringContainsString($name, $source);
        }
    }
}

<?php

use console\components\Task51AppliedSchemaFingerprint;
use yii\db\Migration;

/** MySQL/InnoDB authority for the Task 5.1 global exact-one state machine. */
final class m260828_170000_create_task51_stage_b_coordinator_tables extends Migration
{
    private const TABLE_OPTIONS = 'CHARACTER SET ascii COLLATE ascii_bin ENGINE=InnoDB';

    public function safeUp()
    {
        $this->assertSupportedDatabase();

        $this->createTable('{{%task51_stage_b_execution}}', [
            'execution_id' => $this->string(128)->notNull(),
            'approval_ref' => $this->string(191)->notNull(),
            'stage_b_sha256' => $this->char(64)->notNull(),
            'claim_capability_sha256' => $this->char(64)->notNull(),
            'coordinator_origin' => $this->string(255)->notNull(),
            'coordinator_server_publish_sha' => $this->char(40)->notNull(),
            'state' => $this->string(16)->notNull(),
            'state_version' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'claim_count' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'consumption_count' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'issued_at' => $this->dateTime(3)->notNull(),
            'expires_at' => $this->dateTime(3)->notNull(),
            'claimed_at' => $this->dateTime(3)->null(),
            'consumed_at' => $this->dateTime(3)->null(),
            'claim_receipt_canonical' => $this->binary()->null(),
            'claim_receipt_sha256' => $this->char(64)->null(),
            'runner_export_receipt_ref' => $this->string(512)->null(),
            'runner_export_receipt_sha256' => $this->char(64)->null(),
            'consumption_receipt_canonical' => $this->binary()->null(),
            'consumption_receipt_sha256' => $this->char(64)->null(),
            'created_at' => $this->dateTime(3)->notNull(),
            'updated_at' => $this->dateTime(3)->notNull(),
            'production_direct_matrix_evidence_ref' => $this->string(512)->notNull(),
            'production_direct_matrix_subject_digest' => $this->char(64)->notNull(),
            'PRIMARY KEY ([[execution_id]])',
        ], self::TABLE_OPTIONS);

        $this->createIndex('uq_task51_stage_b_approval_ref', '{{%task51_stage_b_execution}}', 'approval_ref', true);
        $this->createIndex('uq_task51_stage_b_sha256', '{{%task51_stage_b_execution}}', 'stage_b_sha256', true);
        $this->createIndex('uq_task51_stage_b_claim_capability_sha256', '{{%task51_stage_b_execution}}', 'claim_capability_sha256', true);
        $this->createIndex('idx_task51_stage_b_state_expiry', '{{%task51_stage_b_execution}}', ['state', 'expires_at']);
        $this->createIndex('idx_task51_stage_b_runner_export_sha256', '{{%task51_stage_b_execution}}', 'runner_export_receipt_sha256');
        $this->addCheck(
            'ck_task51_stage_b_state',
            '{{%task51_stage_b_execution}}',
            "[[state]] IN ('ISSUED', 'CLAIMED', 'CONSUMED')"
        );
        $this->addCheck(
            'ck_task51_stage_b_counts',
            '{{%task51_stage_b_execution}}',
            "([[state]] = 'ISSUED' AND [[state_version]] = 0 AND [[claim_count]] = 0 AND [[consumption_count]] = 0) "
                . "OR ([[state]] = 'CLAIMED' AND [[state_version]] = 1 AND [[claim_count]] = 1 AND [[consumption_count]] = 0) "
                . "OR ([[state]] = 'CONSUMED' AND [[state_version]] = 2 AND [[claim_count]] = 1 AND [[consumption_count]] = 1)"
        );
        $this->addCheck(
            'ck_task51_stage_b_receipts',
            '{{%task51_stage_b_execution}}',
            "([[state]] = 'ISSUED' AND [[claimed_at]] IS NULL AND [[consumed_at]] IS NULL "
                . "AND [[claim_receipt_canonical]] IS NULL AND [[claim_receipt_sha256]] IS NULL "
                . "AND [[runner_export_receipt_ref]] IS NULL AND [[runner_export_receipt_sha256]] IS NULL "
                . "AND [[consumption_receipt_canonical]] IS NULL AND [[consumption_receipt_sha256]] IS NULL) "
                . "OR ([[state]] = 'CLAIMED' AND [[claimed_at]] IS NOT NULL AND [[consumed_at]] IS NULL "
                . "AND [[claim_receipt_canonical]] IS NOT NULL AND [[claim_receipt_sha256]] IS NOT NULL "
                . "AND [[runner_export_receipt_ref]] IS NULL AND [[runner_export_receipt_sha256]] IS NULL "
                . "AND [[consumption_receipt_canonical]] IS NULL AND [[consumption_receipt_sha256]] IS NULL) "
                . "OR ([[state]] = 'CONSUMED' AND [[claimed_at]] IS NOT NULL AND [[consumed_at]] IS NOT NULL "
                . "AND [[claim_receipt_canonical]] IS NOT NULL AND [[claim_receipt_sha256]] IS NOT NULL "
                . "AND [[runner_export_receipt_ref]] IS NOT NULL AND [[runner_export_receipt_sha256]] IS NOT NULL "
                . "AND [[consumption_receipt_canonical]] IS NOT NULL AND [[consumption_receipt_sha256]] IS NOT NULL)"
        );
        $this->addCheck(
            'ck_task51_stage_b_time_order',
            '{{%task51_stage_b_execution}}',
            "[[issued_at]] < [[expires_at]] "
                . "AND [[created_at]] >= [[issued_at]] AND [[created_at]] < [[expires_at]] "
                . "AND [[updated_at]] >= [[created_at]] AND [[updated_at]] < [[expires_at]] "
                . "AND ([[claimed_at]] IS NULL OR ([[claimed_at]] >= [[issued_at]] AND [[claimed_at]] < [[expires_at]])) "
                . "AND ([[consumed_at]] IS NULL OR ([[claimed_at]] IS NOT NULL "
                . "AND [[consumed_at]] >= [[claimed_at]] AND [[consumed_at]] < [[expires_at]]))"
        );

        $this->createTable('{{%task51_stage_b_transition}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'execution_id' => $this->string(128)->notNull(),
            'ordinal' => $this->smallInteger()->unsigned()->notNull(),
            'from_state' => $this->string(16)->null(),
            'to_state' => $this->string(16)->notNull(),
            'state_version' => $this->integer()->unsigned()->notNull(),
            'evidence_sha256' => $this->char(64)->notNull(),
            'occurred_at' => $this->dateTime(3)->notNull(),
        ], self::TABLE_OPTIONS);

        $this->createIndex(
            'uq_task51_stage_b_transition_ordinal',
            '{{%task51_stage_b_transition}}',
            ['execution_id', 'ordinal'],
            true
        );
        $this->createIndex(
            'idx_task51_stage_b_transition_time',
            '{{%task51_stage_b_transition}}',
            ['occurred_at', 'execution_id']
        );
        $this->addForeignKey(
            'fk_task51_stage_b_transition_execution',
            '{{%task51_stage_b_transition}}',
            'execution_id',
            '{{%task51_stage_b_execution}}',
            'execution_id',
            'RESTRICT',
            'RESTRICT'
        );
        $this->addCheck(
            'ck_task51_stage_b_transition_shape',
            '{{%task51_stage_b_transition}}',
            "([[ordinal]] = 0 AND [[from_state]] IS NULL AND [[to_state]] = 'ISSUED' AND [[state_version]] = 0) "
                . "OR ([[ordinal]] = 1 AND [[from_state]] IS NOT NULL AND [[from_state]] = 'ISSUED' "
                . "AND [[to_state]] = 'CLAIMED' AND [[state_version]] = 1) "
                . "OR ([[ordinal]] = 2 AND [[from_state]] IS NOT NULL AND [[from_state]] = 'CLAIMED' "
                . "AND [[to_state]] = 'CONSUMED' AND [[state_version]] = 2)"
        );
        $this->addCheck(
            'ck_task51_stage_b_transition_evidence_sha256',
            '{{%task51_stage_b_transition}}',
            "[[evidence_sha256]] REGEXP '^[0-9a-f]{64}$'"
        );
        $this->execute(
            "CREATE TRIGGER [[trg_task51_stage_b_transition_no_update]] "
                . "BEFORE UPDATE ON {{%task51_stage_b_transition}} FOR EACH ROW "
                . "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Task 5.1 transition ledger is append-only'"
        );
        $this->execute(
            "CREATE TRIGGER [[trg_task51_stage_b_transition_no_delete]] "
                . "BEFORE DELETE ON {{%task51_stage_b_transition}} FOR EACH ROW "
                . "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Task 5.1 transition ledger is append-only'"
        );
    }

    /**
     * The coordinator ledger is an evidence authority, not disposable schema.
     * Generic migration rollback must never drop it, even while it is empty.
     * Isolated tests own their schema and tear it down explicitly outside this
     * production migration.
     */
    public function safeDown(): bool
    {
        return false;
    }

    /** Fail before any DDL: CHECK enforcement and DROP CHECK both require this floor. */
    private function assertSupportedDatabase(): void
    {
        if ($this->db->tablePrefix !== '') {
            throw new RuntimeException(
                'Task 5.1 migration requires the single unprefixed authority in its database.'
            );
        }
        Task51AppliedSchemaFingerprint::assertSupportedDatabase($this->db);
    }
}

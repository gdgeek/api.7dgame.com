<?php

use yii\db\Migration;

/** Keep account-deleted credentials as permanent tombstones, including their UUID reservation. */
class m260928_150000_preserve_revoked_device_sn extends Migration
{
    public function up()
    {
        // MySQL DDL is not transactional. Perform the FK replacement in one ALTER
        // so account deletion never runs in a window without a foreign key.
        if ($this->db->driverName !== 'mysql') {
            throw new \RuntimeException('This SN migration requires MySQL.');
        }
        $table = $this->db->schema->getTableSchema('{{%device_sn}}', true);
        if (!isset($table->columns['original_user_id'])) {
            $this->addColumn('{{%device_sn}}', 'original_user_id', $this->integer()->null()
                ->comment('Original account ID for audit display only; never an authorization binding'));
        }
        // Idempotent when resuming an interrupted deployment. Pause account
        // deletion and SN generation during migration/rollout to retain history.
        $this->execute('UPDATE {{%device_sn}} SET [[original_user_id]] = [[user_id]]'
            . ' WHERE [[original_user_id]] IS NULL AND [[user_id]] IS NOT NULL');
        // InnoDB rejects dropping and adding the same FK name in one ALTER.
        // Use a new name and recognize it when resuming after completed DDL.
        if (!isset($table->foreignKeys['fk_device_sn_user_retained'])) {
            if (!isset($table->foreignKeys['fk_device_sn_user'])) {
                throw new \RuntimeException('Expected SN account foreign key is missing.');
            }
            $this->execute('ALTER TABLE {{%device_sn}}'
                . ' DROP FOREIGN KEY [[fk_device_sn_user]],'
                . ' MODIFY [[user_id]] INT NULL,'
                . ' ADD CONSTRAINT [[fk_device_sn_user_retained]] FOREIGN KEY ([[user_id]])'
                . ' REFERENCES {{%user}} ([[id]]) ON DELETE SET NULL');
        }
        $this->db->schema->refreshTableSchema('{{%device_sn}}');
        $table = $this->db->schema->getTableSchema('{{%device_sn}}', true);
        $deleteRule = $this->db->createCommand('SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS'
            . ' WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME=:table'
            . ' AND CONSTRAINT_NAME=\'fk_device_sn_user_retained\'',
            [':table' => $table->name])->queryScalar();
        $expectedForeignKey = [0 => $this->db->schema->getRawTableName('{{%user}}'), 'user_id' => 'id'];
        if (!$table->columns['user_id']->allowNull || $deleteRule !== 'SET NULL'
            || ($table->foreignKeys['fk_device_sn_user_retained'] ?? null) !== $expectedForeignKey
            || isset($table->foreignKeys['fk_device_sn_user'])) {
            throw new \RuntimeException('SN account foreign key must retain records with ON DELETE SET NULL.');
        }
    }

    public function down()
    {
        // Restoring CASCADE or reconnecting the historical account ID would
        // erase evidence or resurrect a deleted account's credential.
        echo "This migration cannot be reverted without losing permanent SN revocation history.\n";
        return false;
    }
}

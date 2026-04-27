<?php

use yii\db\Migration;

/**
 * Adds the runtime localization package file reference to space.
 */
class m260427_020000_add_file_id_to_space_table extends Migration
{
    public function safeUp()
    {
        $space = $this->db->schema->getTableSchema('{{%space}}', true);
        if ($space !== null && $space->getColumn('file_id') !== null) {
            return;
        }

        $this->addColumn('{{%space}}', 'file_id', $this->integer());
        $this->createIndex('{{%idx-space-file_id}}', '{{%space}}', 'file_id');
        $this->addForeignKey(
            '{{%fk-space-file_id}}',
            '{{%space}}',
            'file_id',
            '{{%file}}',
            'id',
            'SET NULL'
        );
    }

    public function safeDown()
    {
        $space = $this->db->schema->getTableSchema('{{%space}}', true);
        if ($space === null || $space->getColumn('file_id') === null) {
            return;
        }

        $this->dropForeignKey('{{%fk-space-file_id}}', '{{%space}}');
        $this->dropIndex('{{%idx-space-file_id}}', '{{%space}}');
        $this->dropColumn('{{%space}}', 'file_id');
    }
}

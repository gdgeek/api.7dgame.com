<?php

use yii\db\Migration;

/**
 * Handles adding space snapshot data to table `{{%snapshot}}`.
 */
class m260428_090000_add_space_column_to_snapshot_table extends Migration
{
    public function safeUp()
    {
        $snapshot = $this->db->schema->getTableSchema('{{%snapshot}}', true);
        if ($snapshot !== null && $snapshot->getColumn('space') !== null) {
            return;
        }

        $this->addColumn('{{%snapshot}}', 'space', $this->json());
    }

    public function safeDown()
    {
        $snapshot = $this->db->schema->getTableSchema('{{%snapshot}}', true);
        if ($snapshot === null || $snapshot->getColumn('space') === null) {
            return;
        }

        $this->dropColumn('{{%snapshot}}', 'space');
    }
}

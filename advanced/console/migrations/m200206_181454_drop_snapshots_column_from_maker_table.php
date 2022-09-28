<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%maker}}`.
 */
class m200206_181454_drop_snapshots_column_from_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%maker}}', 'snapshots');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%maker}}', 'snapshots', $this->text());
    }
}

<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%maker}}`.
 */
class m200204_162715_add_snapshots_column_to_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%maker}}', 'snapshots', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%maker}}', 'snapshots');
    }
}

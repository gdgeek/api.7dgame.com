<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%snapshot}}`.
 */
class m251001_141414_add_managers_column_to_snapshot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%snapshot}}', 'managers', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%snapshot}}', 'managers');
    }
}

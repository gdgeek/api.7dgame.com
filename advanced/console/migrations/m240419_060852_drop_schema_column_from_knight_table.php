<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%knight}}`.
 */
class m240419_060852_drop_schema_column_from_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%knight}}', 'schema');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%knight}}', 'schema', $this->json());
    }
}

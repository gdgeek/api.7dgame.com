<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%knight}}`.
 */
class m230809_061222_add_schema_column_to_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%knight}}', 'schema', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%knight}}', 'schema');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%knight}}`.
 */
class m230809_062025_add_events_column_to_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%knight}}', 'events', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%knight}}', 'events');
    }
}

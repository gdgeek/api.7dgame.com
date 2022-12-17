<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta_event}}`.
 */
class m221217_064817_drop_slots_column_from_meta_event_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%meta_event}}', 'slots');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta_event}}', 'slots', $this->json());
    }
}

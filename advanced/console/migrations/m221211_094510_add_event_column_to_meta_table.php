<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta}}`.
 */
class m221211_094510_add_event_column_to_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta}}', 'event', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%meta}}', 'event');
    }
}

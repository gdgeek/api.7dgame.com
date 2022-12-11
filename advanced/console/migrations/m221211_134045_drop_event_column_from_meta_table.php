<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta}}`.
 */
class m221211_134045_drop_event_column_from_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%meta}}', 'event');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta}}', 'event', $this->json());
    }
}

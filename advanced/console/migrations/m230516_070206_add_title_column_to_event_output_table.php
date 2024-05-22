<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%event_output}}`.
 */
class m230516_070206_add_title_column_to_event_output_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%event_output}}', 'title', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%event_output}}', 'title');
    }
}

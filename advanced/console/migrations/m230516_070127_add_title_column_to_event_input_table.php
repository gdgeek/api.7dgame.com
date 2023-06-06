<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%event_input}}`.
 */
class m230516_070127_add_title_column_to_event_input_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%event_input}}', 'title', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%event_input}}', 'title');
    }
}

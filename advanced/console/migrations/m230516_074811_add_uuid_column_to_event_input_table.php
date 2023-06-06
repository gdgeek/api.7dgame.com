<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%event_input}}`.
 */
class m230516_074811_add_uuid_column_to_event_input_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%event_input}}', 'uuid', $this->string()->notNull()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%event_input}}', 'uuid');
    }
}

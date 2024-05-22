<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%event_output}}`.
 */
class m230516_074829_add_uuid_column_to_event_output_table extends Migration
{
    /**
     * {@inheritdoc}
     */#
    public function safeUp()
    {
        $this->addColumn('{{%event_output}}', 'uuid', $this->string()->notNull()->unique());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%event_output}}', 'uuid');
    }
}

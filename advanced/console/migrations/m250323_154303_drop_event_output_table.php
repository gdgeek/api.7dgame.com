<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%event_output}}`.
 */
class m250323_154303_drop_event_output_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%event_output}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%event_output}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

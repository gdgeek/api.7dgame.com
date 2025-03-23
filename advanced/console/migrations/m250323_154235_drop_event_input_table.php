<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%event_input}}`.
 */
class m250323_154235_drop_event_input_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%event_input}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%event_input}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

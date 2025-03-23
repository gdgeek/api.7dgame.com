<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%event_node}}`.
 */
class m250323_154436_drop_event_node_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%event_node}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%event_node}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

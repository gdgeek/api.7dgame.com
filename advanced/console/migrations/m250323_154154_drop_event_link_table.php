<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%event_link}}`.
 */
class m250323_154154_drop_event_link_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%event_link}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%event_link}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

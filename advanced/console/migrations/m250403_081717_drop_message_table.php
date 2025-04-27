<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%message}}`.
 */
class m250403_081717_drop_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%message}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%message}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

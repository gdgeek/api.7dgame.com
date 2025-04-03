<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%message_tags}}`.
 */
class m250403_081413_drop_message_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%message_tags}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%message_tags}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%like}}`.
 */
class m250403_081523_drop_like_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%like}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%like}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

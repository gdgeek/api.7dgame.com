<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%file_store}}`.
 */
class m250403_081749_drop_file_store_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%file_store}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%file_store}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

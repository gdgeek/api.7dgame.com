<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%reply}}`.
 */
class m250403_081612_drop_reply_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%reply}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%reply}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

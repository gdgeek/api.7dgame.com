<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%knight}}`.
 */
class m250323_133538_drop_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%knight}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%knight}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

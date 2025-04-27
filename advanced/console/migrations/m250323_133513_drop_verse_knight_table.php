<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%verse_knight}}`.
 */
class m250323_133513_drop_verse_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%verse_knight}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%verse_knight}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

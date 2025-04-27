<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%verse_release}}`.
 */
class m250403_070330_drop_verse_release_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%verse_release}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%verse_release}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

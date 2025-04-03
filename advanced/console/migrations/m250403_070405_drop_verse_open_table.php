<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%verse_open}}`.
 */
class m250403_070405_drop_verse_open_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%verse_open}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%verse_open}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

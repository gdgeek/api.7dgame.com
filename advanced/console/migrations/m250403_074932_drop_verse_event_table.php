<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%verse_event}}`.
 */
class m250403_074932_drop_verse_event_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%verse_event}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%verse_event}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

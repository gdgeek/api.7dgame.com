<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%verse_share}}`.
 */
class m250403_070305_drop_verse_share_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%verse_share}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%verse_share}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

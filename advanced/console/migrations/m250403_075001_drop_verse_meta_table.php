<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%verse_meta}}`.
 */
class m250403_075001_drop_verse_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%verse_meta}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%verse_meta}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

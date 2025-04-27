<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%verse_space}}`.
 */
class m250403_070034_drop_verse_space_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%verse_space}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%verse_space}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%meta_knight}}`.
 */
class m250323_133432_drop_meta_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%meta_knight}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%meta_knight}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

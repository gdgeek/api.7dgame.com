<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%file_reference}}`.
 */
class m191021_143017_drop_file_reference_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%file_reference}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%file_reference}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

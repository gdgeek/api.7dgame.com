<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%script_data}}`.
 */
class m190803_125110_drop_script_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%script_data}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%script_data}}', [
            'id' => $this->primaryKey(),
        ]);
    }
}

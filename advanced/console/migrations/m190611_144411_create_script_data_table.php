<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%script_data}}`.
 */
class m190611_144411_create_script_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%script_data}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(),
            'script' => $this->text(),
            'context' => $this->string(1024),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%script_data}}');
    }
}

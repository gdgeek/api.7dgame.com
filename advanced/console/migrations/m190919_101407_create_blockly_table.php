<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%blockly}}`.
 */
class m190919_101407_create_blockly_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

		$tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB';

        }


        $this->createTable('{{%blockly}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'title' => $this->string(),
            'block' => $this->text(),
            'lua' => $this->text(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%blockly}}');
    }
}

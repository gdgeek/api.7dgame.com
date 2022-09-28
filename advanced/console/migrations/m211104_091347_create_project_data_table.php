<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_data}}`.
 */
class m211104_091347_create_project_data_table extends Migration
{
   
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    { 
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%project_data}}', [
            'id' => $this->primaryKey(),
            'configuration' => $this->text(),
            'logic' => $this->text(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project_data}}');
    }
}

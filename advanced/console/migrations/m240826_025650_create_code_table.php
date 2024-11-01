<?php

use yii\db\Migration;

/**
* Handles the creation of table `{{%code}}`.
*/
class m240826_025650_create_code_table extends Migration
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
        $this->createTable('{{%code}}', [
            'id' => $this->primaryKey(),
            'lua' => $this->text(),
            'js' => $this->text(),
        ],$tableOptions);
    }
    
    /**
    * {@inheritdoc}
    */
    public function safeDown()
    {
        $this->dropTable('{{%code}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%token}}`.
 */
class m220323_071920_create_token_table extends Migration
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

        $this->createTable('{{%token}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique(),
            'token' => $this->text(),
            'updated_at' => $this->dateTime(),
            'overdue_at' => $this->dateTime(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%token}}');
    }
}

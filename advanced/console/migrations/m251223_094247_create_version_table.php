<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%version}}`.
 */
class m251223_094247_create_version_table extends Migration
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

        $this->createTable('{{%version}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'number' => $this->integer()->notNull()->unique(),
            'created_at' => $this->datetime()->notNull(),
            'info' => $this->json(),
        ],$tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%version}}');
    }
}

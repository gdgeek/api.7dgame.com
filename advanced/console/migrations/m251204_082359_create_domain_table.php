<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%domain}}`.
 */
class m251204_082359_create_domain_table extends Migration
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
        $this->createTable('{{%domain}}', [
            'id' => $this->primaryKey(),
            'domain' => $this->string()->notNull()->unique(),
            'title' => $this->string(),
            'info' => $this->json(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%domain}}');
    }
}

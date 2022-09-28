<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%file_store}}`.
 */
class m220125_100753_create_file_store_table extends Migration
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

        $this->createTable('{{%file_store}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull()->unique(),
            'size' => $this->integer(),
        ], $tableOptions);

        // creates index for column `message_id`
        $this->createIndex(
            '{{%idx-file_store-key}}',
            '{{%file_store}}',
            'key'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-file_store-key}}',
            '{{%file_store}}'
        );

        $this->dropTable('{{%file_store}}');
    }
}

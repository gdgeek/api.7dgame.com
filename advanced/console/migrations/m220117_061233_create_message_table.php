<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%message}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 */
class m220117_061233_create_message_table extends Migration
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

        $this->createTable('{{%message}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'body' => $this->text()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'updater_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp(),
            'info' => $this->json()
        ], $tableOptions);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-message-author_id}}',
            '{{%message}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-message-author_id}}',
            '{{%message}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-message-updater_id}}',
            '{{%message}}',
            'updater_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-message-updater_id}}',
            '{{%message}}',
            'updater_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-message-author_id}}',
            '{{%message}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-message-author_id}}',
            '{{%message}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-message-updater_id}}',
            '{{%message}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-message-updater_id}}',
            '{{%message}}'
        );

        $this->dropTable('{{%message}}');
    }
}

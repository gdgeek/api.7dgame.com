<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reply}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 */
class m220118_061233_create_reply_table extends Migration
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

        $this->createTable('{{%reply}}', [
            'id' => $this->primaryKey(),
            'message_id' => $this->integer()->notNull(),
            'body' => $this->text()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'updater_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp(),
            'info' => $this->json()
        ], $tableOptions);


        // creates index for column `message_id`
        $this->createIndex(
            '{{%idx-reply-message_id}}',
            '{{%reply}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-reply-message_id}}',
            '{{%reply}}',
            'message_id',
            '{{%message}}',
            'id',
            'CASCADE'
        );


        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-reply-author_id}}',
            '{{%reply}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-reply-author_id}}',
            '{{%reply}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-reply-updater_id}}',
            '{{%reply}}',
            'updater_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-reply-updater_id}}',
            '{{%reply}}',
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

        // drops foreign key for table `{{%message}}`
        $this->dropForeignKey(
            '{{%fk-reply-message_id}}',
            '{{%reply}}'
        );

        // drops index for column `message_id`
        $this->dropIndex(
            '{{%idx-reply-message_id}}',
            '{{%reply}}'
        );


        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-reply-author_id}}',
            '{{%reply}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-reply-author_id}}',
            '{{%reply}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-reply-updater_id}}',
            '{{%reply}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-reply-updater_id}}',
            '{{%reply}}'
        );

        $this->dropTable('{{%reply}}');
    }
}

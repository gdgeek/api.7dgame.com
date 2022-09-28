<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%like}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%message}}`
 */
class m220317_071805_create_like_table extends Migration
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

        $this->createTable('{{%like}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'message_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-like-user_id}}',
            '{{%like}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-like-user_id}}',
            '{{%like}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `message_id`
        $this->createIndex(
            '{{%idx-like-message_id}}',
            '{{%like}}',
            'message_id'
        );

        // add foreign key for table `{{%message}}`
        $this->addForeignKey(
            '{{%fk-like-message_id}}',
            '{{%like}}',
            'message_id',
            '{{%message}}',
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
            '{{%fk-like-user_id}}',
            '{{%like}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-like-user_id}}',
            '{{%like}}'
        );

        // drops foreign key for table `{{%message}}`
        $this->dropForeignKey(
            '{{%fk-like-message_id}}',
            '{{%like}}'
        );

        // drops index for column `message_id`
        $this->dropIndex(
            '{{%idx-like-message_id}}',
            '{{%like}}'
        );

        $this->dropTable('{{%like}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%message_tags}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%message}}`
 * - `{{%tags}}`
 */
class m220217_073405_create_message_tags_table extends Migration
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

        $this->createTable('{{%message_tags}}', [
            'id' => $this->primaryKey(),
            'message_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `message_id`
        $this->createIndex(
            '{{%idx-message_tags-message_id}}',
            '{{%message_tags}}',
            'message_id'
        );

        // add foreign key for table `{{%message}}`
        $this->addForeignKey(
            '{{%fk-message_tags-message_id}}',
            '{{%message_tags}}',
            'message_id',
            '{{%message}}',
            'id',
            'CASCADE'
        );

        // creates index for column `tag_id`
        $this->createIndex(
            '{{%idx-message_tags-tag_id}}',
            '{{%message_tags}}',
            'tag_id'
        );

        // add foreign key for table `{{%tags}}`
        $this->addForeignKey(
            '{{%fk-message_tags-tag_id}}',
            '{{%message_tags}}',
            'tag_id',
            '{{%tags}}',
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
            '{{%fk-message_tags-message_id}}',
            '{{%message_tags}}'
        );

        // drops index for column `message_id`
        $this->dropIndex(
            '{{%idx-message_tags-message_id}}',
            '{{%message_tags}}'
        );

        // drops foreign key for table `{{%tags}}`
        $this->dropForeignKey(
            '{{%fk-message_tags-tag_id}}',
            '{{%message_tags}}'
        );

        // drops index for column `tag_id`
        $this->dropIndex(
            '{{%idx-message_tags-tag_id}}',
            '{{%message_tags}}'
        );

        $this->dropTable('{{%message_tags}}');
    }
}

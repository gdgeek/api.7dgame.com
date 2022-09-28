<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_open}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%user}}`
 */
class m220315_061111_create_verse_open_table extends Migration
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

        $this->createTable('{{%verse_open}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'message_id' => $this->integer(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_open-verse_id}}',
            '{{%verse_open}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_open-verse_id}}',
            '{{%verse_open}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

// creates index for column `user_id`
        $this->createIndex(
            '{{%idx-verse_open-user_id}}',
            '{{%verse_open}}',
            'user_id'
        );

// add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-verse_open-user_id}}',
            '{{%verse_open}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `message_id`
        $this->createIndex(
            '{{%idx-verse_open-message_id}}',
            '{{%verse_open}}',
            'message_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-verse_open-message_id}}',
            '{{%verse_open}}',
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
        // drops foreign key for table `{{%verse}}`
        $this->dropForeignKey(
            '{{%fk-verse_open-verse_id}}',
            '{{%verse_open}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_open-verse_id}}',
            '{{%verse_open}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-verse_open-user_id}}',
            '{{%verse_open}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-verse_open-user_id}}',
            '{{%verse_open}}'
        );

        // drops foreign key for table `{{%message}}`
        $this->dropForeignKey(
            '{{%fk-verse_open-message_id}}',
            '{{%verse_open}}'
        );

        // drops index for column `message_id`
        $this->dropIndex(
            '{{%idx-verse_open-message_id}}',
            '{{%verse_open}}'
        );

        $this->dropTable('{{%verse_open}}');
    }
}

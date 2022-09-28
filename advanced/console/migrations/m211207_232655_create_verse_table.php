<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 * - `{{%file}}`
 */
class m211207_232655_create_verse_table extends Migration
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
        $this->createTable('{{%verse}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'updater_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp(),
            'name' => $this->string()->notNull(),
            'info' => $this->json(),
            'image' => $this->integer(),
            'data' => $this->json(),
        ], $tableOptions);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-verse-author_id}}',
            '{{%verse}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-verse-author_id}}',
            '{{%verse}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            '{{%idx-verse-updater_id}}',
            '{{%verse}}',
            'updater_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-verse-updater_id}}',
            '{{%verse}}',
            'updater_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image`
        $this->createIndex(
            '{{%idx-verse-image}}',
            '{{%verse}}',
            'image'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-verse-image}}',
            '{{%verse}}',
            'image',
            '{{%file}}',
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
            '{{%fk-verse-author_id}}',
            '{{%verse}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-verse-author_id}}',
            '{{%verse}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-verse-updater_id}}',
            '{{%verse}}'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            '{{%idx-verse-updater_id}}',
            '{{%verse}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-verse-image}}',
            '{{%verse}}'
        );

        // drops index for column `image`
        $this->dropIndex(
            '{{%idx-verse-image}}',
            '{{%verse}}'
        );

        $this->dropTable('{{%verse}}');
    }
}

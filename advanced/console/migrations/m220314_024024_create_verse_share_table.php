<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_share}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%user}}`
 */
class m220314_024024_create_verse_share_table extends Migration
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

        $this->createTable('{{%verse_share}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'info' => $this->json(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_share-verse_id}}',
            '{{%verse_share}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_share-verse_id}}',
            '{{%verse_share}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-verse_share-user_id}}',
            '{{%verse_share}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-verse_share-user_id}}',
            '{{%verse_share}}',
            'user_id',
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
        // drops foreign key for table `{{%verse}}`
        $this->dropForeignKey(
            '{{%fk-verse_share-verse_id}}',
            '{{%verse_share}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_share-verse_id}}',
            '{{%verse_share}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-verse_share-user_id}}',
            '{{%verse_share}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-verse_share-user_id}}',
            '{{%verse_share}}'
        );

        $this->dropTable('{{%verse_share}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_sknight}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%knight}}`
 */
class m230222_110437_create_verse_knight_table extends Migration
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
        $this->createTable('{{%verse_knight}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'knight_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_knight-verse_id}}',
            '{{%verse_knight}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_knight-verse_id}}',
            '{{%verse_knight}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `knight_id`
        $this->createIndex(
            '{{%idx-verse_knight-knight_id}}',
            '{{%verse_knight}}',
            'knight_id'
        );

        // add foreign key for table `{{%knight}}`
        $this->addForeignKey(
            '{{%fk-verse_knight-knight_id}}',
            '{{%verse_knight}}',
            'knight_id',
            '{{%knight}}',
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
            '{{%fk-verse_knight-verse_id}}',
            '{{%verse_knight}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_knight-verse_id}}',
            '{{%verse_knight}}'
        );

        // drops foreign key for table `{{%knight}}`
        $this->dropForeignKey(
            '{{%fk-verse_knight-knight_id}}',
            '{{%verse_knight}}'
        );

        // drops index for column `knight_id`
        $this->dropIndex(
            '{{%idx-verse_knight-knight_id}}',
            '{{%verse_knight}}'
        );

        $this->dropTable('{{%verse_knight}}');
    }
}

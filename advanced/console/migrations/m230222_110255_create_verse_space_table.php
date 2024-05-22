<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_space}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%space}}`
 */
class m230222_110255_create_verse_space_table extends Migration
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
        $this->createTable('{{%verse_space}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'space_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_space-verse_id}}',
            '{{%verse_space}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_space-verse_id}}',
            '{{%verse_space}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `space_id`
        $this->createIndex(
            '{{%idx-verse_space-space_id}}',
            '{{%verse_space}}',
            'space_id'
        );

        // add foreign key for table `{{%space}}`
        $this->addForeignKey(
            '{{%fk-verse_space-space_id}}',
            '{{%verse_space}}',
            'space_id',
            '{{%space}}',
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
            '{{%fk-verse_space-verse_id}}',
            '{{%verse_space}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_space-verse_id}}',
            '{{%verse_space}}'
        );

        // drops foreign key for table `{{%space}}`
        $this->dropForeignKey(
            '{{%fk-verse_space-space_id}}',
            '{{%verse_space}}'
        );

        // drops index for column `space_id`
        $this->dropIndex(
            '{{%idx-verse_space-space_id}}',
            '{{%verse_space}}'
        );

        $this->dropTable('{{%verse_space}}');
    }
}

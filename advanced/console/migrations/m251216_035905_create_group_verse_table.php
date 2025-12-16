<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_verse}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 * - `{{%group}}`
 */
class m251216_035905_create_group_verse_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_verse}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'group_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-group_verse-verse_id}}',
            '{{%group_verse}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-group_verse-verse_id}}',
            '{{%group_verse}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_id`
        $this->createIndex(
            '{{%idx-group_verse-group_id}}',
            '{{%group_verse}}',
            'group_id'
        );

        // add foreign key for table `{{%group}}`
        $this->addForeignKey(
            '{{%fk-group_verse-group_id}}',
            '{{%group_verse}}',
            'group_id',
            '{{%group}}',
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
            '{{%fk-group_verse-verse_id}}',
            '{{%group_verse}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-group_verse-verse_id}}',
            '{{%group_verse}}'
        );

        // drops foreign key for table `{{%group}}`
        $this->dropForeignKey(
            '{{%fk-group_verse-group_id}}',
            '{{%group_verse}}'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            '{{%idx-group_verse-group_id}}',
            '{{%group_verse}}'
        );

        $this->dropTable('{{%group_verse}}');
    }
}

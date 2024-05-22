<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%event_link}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m230516_153312_add_verse_id_column_to_event_link_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%event_link}}', 'verse_id', $this->integer()->notNull());

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-event_link-verse_id}}',
            '{{%event_link}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-event_link-verse_id}}',
            '{{%event_link}}',
            'verse_id',
            '{{%verse}}',
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
            '{{%fk-event_link-verse_id}}',
            '{{%event_link}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-event_link-verse_id}}',
            '{{%event_link}}'
        );

        $this->dropColumn('{{%event_link}}', 'verse_id');
    }
}

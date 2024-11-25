<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%verse_open}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%message}}`
 */
class m241023_093206_drop_message_id_column_from_verse_open_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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

        $this->dropColumn('{{%verse_open}}', 'message_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%verse_open}}', 'message_id', $this->integer()->notNull());

        // creates index for column `message_id`
        $this->createIndex(
            '{{%idx-verse_open-message_id}}',
            '{{%verse_open}}',
            'message_id'
        );

        // add foreign key for table `{{%message}}`
        $this->addForeignKey(
            '{{%fk-verse_open-message_id}}',
            '{{%verse_open}}',
            'message_id',
            '{{%message}}',
            'id',
            'CASCADE'
        );
    }
}

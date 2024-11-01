<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%verse_event}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m221216_051715_create_verse_event_table extends Migration
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
        $this->createTable('{{%verse_event}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
            'data' => $this->json(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-verse_event-verse_id}}',
            '{{%verse_event}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-verse_event-verse_id}}',
            '{{%verse_event}}',
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
            '{{%fk-verse_event-verse_id}}',
            '{{%verse_event}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-verse_event-verse_id}}',
            '{{%verse_event}}'
        );

        $this->dropTable('{{%verse_event}}');
    }
}

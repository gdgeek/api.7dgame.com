<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%event_node}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m230515_141411_create_event_node_table extends Migration
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
        $this->createTable('{{%event_node}}', [
            'id' => $this->primaryKey(),
            'verse_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-event_node-verse_id}}',
            '{{%event_node}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-event_node-verse_id}}',
            '{{%event_node}}',
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
            '{{%fk-event_node-verse_id}}',
            '{{%event_node}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-event_node-verse_id}}',
            '{{%event_node}}'
        );

        $this->dropTable('{{%event_node}}');
    }
}

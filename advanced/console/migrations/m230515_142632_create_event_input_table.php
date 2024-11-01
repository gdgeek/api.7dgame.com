<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%event_input}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%event_node}}`
 */
class m230515_142632_create_event_input_table extends Migration
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
        $this->createTable('{{%event_input}}', [
            'id' => $this->primaryKey(),
            'event_node_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `event_node_id`
        $this->createIndex(
            '{{%idx-event_input-event_node_id}}',
            '{{%event_input}}',
            'event_node_id'
        );

        // add foreign key for table `{{%event_node}}`
        $this->addForeignKey(
            '{{%fk-event_input-event_node_id}}',
            '{{%event_input}}',
            'event_node_id',
            '{{%event_node}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%event_node}}`
        $this->dropForeignKey(
            '{{%fk-event_input-event_node_id}}',
            '{{%event_input}}'
        );

        // drops index for column `event_node_id`
        $this->dropIndex(
            '{{%idx-event_input-event_node_id}}',
            '{{%event_input}}'
        );

        $this->dropTable('{{%event_input}}');
    }
}

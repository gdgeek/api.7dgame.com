<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta_knight}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%event_node}}`
 */
class m230628_063701_add_event_node_id_column_to_meta_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta_knight}}', 'event_node_id', $this->integer());

        // creates index for column `event_node_id`
        $this->createIndex(
            '{{%idx-meta_knight-event_node_id}}',
            '{{%meta_knight}}',
            'event_node_id'
        );

        // add foreign key for table `{{%event_node}}`
        $this->addForeignKey(
            '{{%fk-meta_knight-event_node_id}}',
            '{{%meta_knight}}',
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
            '{{%fk-meta_knight-event_node_id}}',
            '{{%meta_knight}}'
        );

        // drops index for column `event_node_id`
        $this->dropIndex(
            '{{%idx-meta_knight-event_node_id}}',
            '{{%meta_knight}}'
        );

        $this->dropColumn('{{%meta_knight}}', 'event_node_id');
    }
}

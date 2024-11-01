<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%event_node}}`
 */
class m240423_100327_drop_event_node_id_column_from_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%event_node}}`
        $this->dropForeignKey(
            '{{%fk-meta-event_node_id}}',
            '{{%meta}}'
        );

        // drops index for column `event_node_id`
        $this->dropIndex(
            '{{%idx-meta-event_node_id}}',
            '{{%meta}}'
        );

        $this->dropColumn('{{%meta}}', 'event_node_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta}}', 'event_node_id', $this->integer());

        // creates index for column `event_node_id`
        $this->createIndex(
            '{{%idx-meta-event_node_id}}',
            '{{%meta}}',
            'event_node_id'
        );

        // add foreign key for table `{{%event_node}}`
        $this->addForeignKey(
            '{{%fk-meta-event_node_id}}',
            '{{%meta}}',
            'event_node_id',
            '{{%event_node}}',
            'id',
            'CASCADE'
        );
    }
}

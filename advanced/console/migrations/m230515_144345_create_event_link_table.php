<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%event_link}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%event_input}}`
 * - `{{%event_output}}`
 */
class m230515_144345_create_event_link_table extends Migration
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
        $this->createTable('{{%event_link}}', [
            'id' => $this->primaryKey(),
            'event_input_id' => $this->integer()->notNull(),
            'event_output_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `event_input_id`
        $this->createIndex(
            '{{%idx-event_link-event_input_id}}',
            '{{%event_link}}',
            'event_input_id'
        );

        // add foreign key for table `{{%event_input}}`
        $this->addForeignKey(
            '{{%fk-event_link-event_input_id}}',
            '{{%event_link}}',
            'event_input_id',
            '{{%event_input}}',
            'id',
            'CASCADE'
        );

        // creates index for column `event_output_id`
        $this->createIndex(
            '{{%idx-event_link-event_output_id}}',
            '{{%event_link}}',
            'event_output_id'
        );

        // add foreign key for table `{{%event_output}}`
        $this->addForeignKey(
            '{{%fk-event_link-event_output_id}}',
            '{{%event_link}}',
            'event_output_id',
            '{{%event_output}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%event_input}}`
        $this->dropForeignKey(
            '{{%fk-event_link-event_input_id}}',
            '{{%event_link}}'
        );

        // drops index for column `event_input_id`
        $this->dropIndex(
            '{{%idx-event_link-event_input_id}}',
            '{{%event_link}}'
        );

        // drops foreign key for table `{{%event_output}}`
        $this->dropForeignKey(
            '{{%fk-event_link-event_output_id}}',
            '{{%event_link}}'
        );

        // drops index for column `event_output_id`
        $this->dropIndex(
            '{{%idx-event_link-event_output_id}}',
            '{{%event_link}}'
        );

        $this->dropTable('{{%event_link}}');
    }
}

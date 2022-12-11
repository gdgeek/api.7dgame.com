<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meta_event}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%meta}}`
 */
class m221211_134317_create_meta_event_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%meta_event}}', [
            'id' => $this->primaryKey(),
            'meta_id' => $this->integer()->notNull(),
            'slots' => $this->json(),
            'links' => $this->json(),
        ]);

        // creates index for column `meta_id`
        $this->createIndex(
            '{{%idx-meta_event-meta_id}}',
            '{{%meta_event}}',
            'meta_id'
        );

        // add foreign key for table `{{%meta}}`
        $this->addForeignKey(
            '{{%fk-meta_event-meta_id}}',
            '{{%meta_event}}',
            'meta_id',
            '{{%meta}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%meta}}`
        $this->dropForeignKey(
            '{{%fk-meta_event-meta_id}}',
            '{{%meta_event}}'
        );

        // drops index for column `meta_id`
        $this->dropIndex(
            '{{%idx-meta_event-meta_id}}',
            '{{%meta_event}}'
        );

        $this->dropTable('{{%meta_event}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%knight}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m221108_172315_add_image_id_column_to_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%knight}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-knight-image_id}}',
            '{{%knight}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-knight-image_id}}',
            '{{%knight}}',
            'image_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-knight-image_id}}',
            '{{%knight}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-knight-image_id}}',
            '{{%knight}}'
        );

        $this->dropColumn('{{%knight}}', 'image_id');
    }
}

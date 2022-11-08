<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%knight}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%resource}}`
 */
class m221108_172213_drop_image_id_column_from_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%resource}}`
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%knight}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-knight-image_id}}',
            '{{%knight}}',
            'image_id'
        );

        // add foreign key for table `{{%resource}}`
        $this->addForeignKey(
            '{{%fk-knight-image_id}}',
            '{{%knight}}',
            'image_id',
            '{{%resource}}',
            'id',
            'CASCADE'
        );
    }
}

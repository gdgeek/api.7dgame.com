<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%polygen}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m210516_132930_add_image_id_column_to_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%polygen}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-polygen-image_id}}',
            '{{%polygen}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-polygen-image_id}}',
            '{{%polygen}}',
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
            '{{%fk-polygen-image_id}}',
            '{{%polygen}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-polygen-image_id}}',
            '{{%polygen}}'
        );

        $this->dropColumn('{{%polygen}}', 'image_id');
    }
}

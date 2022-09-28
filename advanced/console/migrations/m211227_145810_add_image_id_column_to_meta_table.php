<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m211227_145810_add_image_id_column_to_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-meta-image_id}}',
            '{{%meta}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-meta-image_id}}',
            '{{%meta}}',
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
            '{{%fk-meta-image_id}}',
            '{{%meta}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-meta-image_id}}',
            '{{%meta}}'
        );

        $this->dropColumn('{{%meta}}', 'image_id');
    }
}

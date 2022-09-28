<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%picture}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m210521_171717_add_image_id_column_to_picture_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%picture}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-picture-image_id}}',
            '{{%picture}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-picture-image_id}}',
            '{{%picture}}',
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
            '{{%fk-picture-image_id}}',
            '{{%picture}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-picture-image_id}}',
            '{{%picture}}'
        );

        $this->dropColumn('{{%picture}}', 'image_id');
    }
}

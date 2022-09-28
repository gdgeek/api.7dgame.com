<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%verse}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m211227_145658_add_image_id_column_to_verse_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%verse}}', 'image_id', $this->integer());

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-verse-image_id}}',
            '{{%verse}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-verse-image_id}}',
            '{{%verse}}',
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
            '{{%fk-verse-image_id}}',
            '{{%verse}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-verse-image_id}}',
            '{{%verse}}'
        );

        $this->dropColumn('{{%verse}}', 'image_id');
    }
}

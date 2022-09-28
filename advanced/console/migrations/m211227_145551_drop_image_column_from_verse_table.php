<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%verse}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m211227_145551_drop_image_column_from_verse_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-verse-image}}',
            '{{%verse}}'
        );

        // drops index for column `image`
        $this->dropIndex(
            '{{%idx-verse-image}}',
            '{{%verse}}'
        );

        $this->dropColumn('{{%verse}}', 'image');
    }
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%verse}}', 'image', $this->integer());

        // creates index for column `image`
        $this->createIndex(
            '{{%idx-verse-image}}',
            '{{%verse}}',
            'image'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-verse-image}}',
            '{{%verse}}',
            'image',
            '{{%file}}',
            'id',
            'CASCADE'
        );
    }
}

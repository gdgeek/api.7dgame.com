<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m211227_145802_drop_image_column_from_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-meta-image}}',
            '{{%meta}}'
        );

        // drops index for column `image`
        $this->dropIndex(
            '{{%idx-meta-image}}',
            '{{%meta}}'
        );

        $this->dropColumn('{{%meta}}', 'image');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta}}', 'image', $this->integer());

        // creates index for column `image`
        $this->createIndex(
            '{{%idx-meta-image}}',
            '{{%meta}}',
            'image'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-meta-image}}',
            '{{%meta}}',
            'image',
            '{{%file}}',
            'id',
            'CASCADE'
        );
    }
}

<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m240419_092812_add_verse_id_column_to_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta}}', 'verse_id', $this->integer());

        // creates index for column `verse_id`
        $this->createIndex(
            '{{%idx-meta-verse_id}}',
            '{{%meta}}',
            'verse_id'
        );

        // add foreign key for table `{{%verse}}`
        $this->addForeignKey(
            '{{%fk-meta-verse_id}}',
            '{{%meta}}',
            'verse_id',
            '{{%verse}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%verse}}`
        $this->dropForeignKey(
            '{{%fk-meta-verse_id}}',
            '{{%meta}}'
        );

        // drops index for column `verse_id`
        $this->dropIndex(
            '{{%idx-meta-verse_id}}',
            '{{%meta}}'
        );

        $this->dropColumn('{{%meta}}', 'verse_id');
    }
}

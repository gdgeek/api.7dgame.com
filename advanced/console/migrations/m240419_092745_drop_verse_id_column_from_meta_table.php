<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%meta}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%verse}}`
 */
class m240419_092745_drop_verse_id_column_from_meta_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%meta}}', 'verse_id', $this->integer()->notNull());

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
}

<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%polygen}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 */
class m191103_065922_add_file_id_column_to_polygen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%polygen}}', 'file_id', $this->integer());

        // creates index for column `file_id`
        $this->createIndex(
            '{{%idx-polygen-file_id}}',
            '{{%polygen}}',
            'file_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-polygen-file_id}}',
            '{{%polygen}}',
            'file_id',
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
            '{{%fk-polygen-file_id}}',
            '{{%polygen}}'
        );

        // drops index for column `file_id`
        $this->dropIndex(
            '{{%idx-polygen-file_id}}',
            '{{%polygen}}'
        );

        $this->dropColumn('{{%polygen}}', 'file_id');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%meta_knight}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%meta}}`
 */
class m240419_085911_add_meta_id_column_to_meta_knight_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%meta_knight}}', 'meta_id', $this->integer());

        // creates index for column `meta_id`
        $this->createIndex(
            '{{%idx-meta_knight-meta_id}}',
            '{{%meta_knight}}',
            'meta_id'
        );

        // add foreign key for table `{{%meta}}`
        $this->addForeignKey(
            '{{%fk-meta_knight-meta_id}}',
            '{{%meta_knight}}',
            'meta_id',
            '{{%meta}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%meta}}`
        $this->dropForeignKey(
            '{{%fk-meta_knight-meta_id}}',
            '{{%meta_knight}}'
        );

        // drops index for column `meta_id`
        $this->dropIndex(
            '{{%idx-meta_knight-meta_id}}',
            '{{%meta_knight}}'
        );

        $this->dropColumn('{{%meta_knight}}', 'meta_id');
    }
}

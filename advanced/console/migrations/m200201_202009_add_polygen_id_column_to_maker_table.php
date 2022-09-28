<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%maker}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%polygen}}`
 */
class m200201_202009_add_polygen_id_column_to_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%maker}}', 'polygen_id', $this->integer()->notNull());

        // creates index for column `polygen_id`
        $this->createIndex(
            '{{%idx-maker-polygen_id}}',
            '{{%maker}}',
            'polygen_id'
        );

        // add foreign key for table `{{%polygen}}`
        $this->addForeignKey(
            '{{%fk-maker-polygen_id}}',
            '{{%maker}}',
            'polygen_id',
            '{{%polygen}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%polygen}}`
        $this->dropForeignKey(
            '{{%fk-maker-polygen_id}}',
            '{{%maker}}'
        );

        // drops index for column `polygen_id`
        $this->dropIndex(
            '{{%idx-maker-polygen_id}}',
            '{{%maker}}'
        );

        $this->dropColumn('{{%maker}}', 'polygen_id');
    }
}

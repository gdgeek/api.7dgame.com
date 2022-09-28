<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%maker}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%programme}}`
 */
class m200311_132647_add_programme_id_column_to_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%maker}}', 'programme_id', $this->integer());

        // creates index for column `programme_id`
        $this->createIndex(
            '{{%idx-maker-programme_id}}',
            '{{%maker}}',
            'programme_id'
        );

        // add foreign key for table `{{%programme}}`
        $this->addForeignKey(
            '{{%fk-maker-programme_id}}',
            '{{%maker}}',
            'programme_id',
            '{{%programme}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%programme}}`
        $this->dropForeignKey(
            '{{%fk-maker-programme_id}}',
            '{{%maker}}'
        );

        // drops index for column `programme_id`
        $this->dropIndex(
            '{{%idx-maker-programme_id}}',
            '{{%maker}}'
        );

        $this->dropColumn('{{%maker}}', 'programme_id');
    }
}

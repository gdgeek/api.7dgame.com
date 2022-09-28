<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%maker}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%project}}`
 */
class m200304_152138_add_project_id_column_to_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%maker}}', 'project_id', $this->integer());

        // creates index for column `project_id`
        $this->createIndex(
            '{{%idx-maker-project_id}}',
            '{{%maker}}',
            'project_id'
        );

        // add foreign key for table `{{%project}}`
        $this->addForeignKey(
            '{{%fk-maker-project_id}}',
            '{{%maker}}',
            'project_id',
            '{{%project}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%project}}`
        $this->dropForeignKey(
            '{{%fk-maker-project_id}}',
            '{{%maker}}'
        );

        // drops index for column `project_id`
        $this->dropIndex(
            '{{%idx-maker-project_id}}',
            '{{%maker}}'
        );

        $this->dropColumn('{{%maker}}', 'project_id');
    }
}

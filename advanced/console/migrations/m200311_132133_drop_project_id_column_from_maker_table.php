<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%maker}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%project}}`
 */
class m200311_132133_drop_project_id_column_from_maker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
}

<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%project}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%programme}}`
 */
class m200312_122903_add_programme_id_column_to_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'programme_id', $this->integer());

        // creates index for column `programme_id`
        $this->createIndex(
            '{{%idx-project-programme_id}}',
            '{{%project}}',
            'programme_id'
        );

        // add foreign key for table `{{%programme}}`
        $this->addForeignKey(
            '{{%fk-project-programme_id}}',
            '{{%project}}',
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
            '{{%fk-project-programme_id}}',
            '{{%project}}'
        );

        // drops index for column `programme_id`
        $this->dropIndex(
            '{{%idx-project-programme_id}}',
            '{{%project}}'
        );

        $this->dropColumn('{{%project}}', 'programme_id');
    }
}

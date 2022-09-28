<?php

use yii\db\Migration;

/**
 * Handles adding user_id to table `{{%project}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m190527_063623_add_user_id_column_to_project_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'user_id', $this->integer());

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-project-user_id}}',
            '{{%project}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-project-user_id}}',
            '{{%project}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-project-user_id}}',
            '{{%project}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-project-user_id}}',
            '{{%project}}'
        );

        $this->dropColumn('{{%project}}', 'user_id');
    }
}

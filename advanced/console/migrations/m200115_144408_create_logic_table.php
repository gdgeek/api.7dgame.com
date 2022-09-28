<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%logic}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%project}}`
 * - `{{%user}}`
 */
class m200115_144408_create_logic_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%logic}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull(),
            'node_id' => $this->integer(),
            'user_id' => $this->integer()->notNull(),
            'dom' => $this->text(),
            'code' => $this->text(),
        ]);

        // creates index for column `project_id`
        $this->createIndex(
            '{{%idx-logic-project_id}}',
            '{{%logic}}',
            'project_id'
        );

        // add foreign key for table `{{%project}}`
        $this->addForeignKey(
            '{{%fk-logic-project_id}}',
            '{{%logic}}',
            'project_id',
            '{{%project}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-logic-user_id}}',
            '{{%logic}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-logic-user_id}}',
            '{{%logic}}',
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
        // drops foreign key for table `{{%project}}`
        $this->dropForeignKey(
            '{{%fk-logic-project_id}}',
            '{{%logic}}'
        );

        // drops index for column `project_id`
        $this->dropIndex(
            '{{%idx-logic-project_id}}',
            '{{%logic}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-logic-user_id}}',
            '{{%logic}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-logic-user_id}}',
            '{{%logic}}'
        );

        $this->dropTable('{{%logic}}');
    }
}

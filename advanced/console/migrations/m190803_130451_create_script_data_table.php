<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%script_data}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%project}}`
 * - `{{%user}}`
 */
class m190803_130451_create_script_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%script_data}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull()->unique(),
            'user_id' => $this->integer()->notNull(),
            'dom' => $this->text(),
            'code' => $this->text(),
        ]);

        // creates index for column `project_id`
        $this->createIndex(
            '{{%idx-script_data-project_id}}',
            '{{%script_data}}',
            'project_id'
        );

        // add foreign key for table `{{%project}}`
        $this->addForeignKey(
            '{{%fk-script_data-project_id}}',
            '{{%script_data}}',
            'project_id',
            '{{%project}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-script_data-user_id}}',
            '{{%script_data}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-script_data-user_id}}',
            '{{%script_data}}',
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
            '{{%fk-script_data-project_id}}',
            '{{%script_data}}'
        );

        // drops index for column `project_id`
        $this->dropIndex(
            '{{%idx-script_data-project_id}}',
            '{{%script_data}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-script_data-user_id}}',
            '{{%script_data}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-script_data-user_id}}',
            '{{%script_data}}'
        );

        $this->dropTable('{{%script_data}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%editor_data}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%project}}`
 * - `{{%user}}`
 */
class m190612_174335_create_editor_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%editor_data}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer(),
            'user_id' => $this->integer(),
            'node_id' => $this->integer(),
            'type' => $this->string(),
            'data' => $this->text(),
        ]);


		 // creates index for column `project_id`
        $this->createIndex(
            '{{%idx-editor_data-node_id}}',
            '{{%editor_data}}',
            'node_id'
        );

        // creates index for column `project_id`
        $this->createIndex(
            '{{%idx-editor_data-project_id}}',
            '{{%editor_data}}',
            'project_id'
        );

        // add foreign key for table `{{%project}}`
        $this->addForeignKey(
            '{{%fk-editor_data-project_id}}',
            '{{%editor_data}}',
            'project_id',
            '{{%project}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-editor_data-user_id}}',
            '{{%editor_data}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-editor_data-user_id}}',
            '{{%editor_data}}',
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

	// drops index for column `project_id`
        $this->dropIndex(
            '{{%idx-editor_data-node_id}}',
            '{{%editor_data}}'
        );

        // drops foreign key for table `{{%project}}`
        $this->dropForeignKey(
            '{{%fk-editor_data-project_id}}',
            '{{%editor_data}}'
        );

        // drops index for column `project_id`
        $this->dropIndex(
            '{{%idx-editor_data-project_id}}',
            '{{%editor_data}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-editor_data-user_id}}',
            '{{%editor_data}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-editor_data-user_id}}',
            '{{%editor_data}}'
        );

        $this->dropTable('{{%editor_data}}');
    }
}

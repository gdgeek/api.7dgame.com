<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%debug}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 */
class m190604_145824_create_debug_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%debug}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'body' => $this->text(),
            'submitter_id' => $this->integer(),
            'solver_id' => $this->integer(),
            'reply' => $this->text(),
        ]);

        // creates index for column `submitter_id`
        $this->createIndex(
            '{{%idx-debug-submitter_id}}',
            '{{%debug}}',
            'submitter_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-debug-submitter_id}}',
            '{{%debug}}',
            'submitter_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `solver_id`
        $this->createIndex(
            '{{%idx-debug-solver_id}}',
            '{{%debug}}',
            'solver_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-debug-solver_id}}',
            '{{%debug}}',
            'solver_id',
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
            '{{%fk-debug-submitter_id}}',
            '{{%debug}}'
        );

        // drops index for column `submitter_id`
        $this->dropIndex(
            '{{%idx-debug-submitter_id}}',
            '{{%debug}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-debug-solver_id}}',
            '{{%debug}}'
        );

        // drops index for column `solver_id`
        $this->dropIndex(
            '{{%idx-debug-solver_id}}',
            '{{%debug}}'
        );

        $this->dropTable('{{%debug}}');
    }
}

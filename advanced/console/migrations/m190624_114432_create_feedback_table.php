<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%feedback}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%user}}`
 * - `{{%feedback_state}}`
 */
class m190624_114432_create_feedback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%feedback}}', [
            'id' => $this->primaryKey(),
            'reporter' => $this->integer()->notNull(),
            'repairer' => $this->integer(),
            'state_id' => $this->integer(),
            'describe_id' => $this->integer()->notNull(),
            'bug' => $this->text()->notNull(),
            'debug' => $this->text(),
            'infomation' => $this->text(),
            'create_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'fixed_at' => $this->timestamp(),
        ]);
		


        // creates index for column `reporter`
        $this->createIndex(
            '{{%idx-feedback-reporter}}',
            '{{%feedback}}',
            'reporter'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-feedback-reporter}}',
            '{{%feedback}}',
            'reporter',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `repairer`
        $this->createIndex(
            '{{%idx-feedback-repairer}}',
            '{{%feedback}}',
            'repairer'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-feedback-repairer}}',
            '{{%feedback}}',
            'repairer',
            '{{%user}}',
            'id',
            'CASCADE'
        );
		
        // creates index for column `describe_id`
        $this->createIndex(
            '{{%idx-feedback-state_id}}',
            '{{%feedback}}',
            'state_id'
        );

        // add foreign key for table `{{%feedback_state}}`
        $this->addForeignKey(
            '{{%fk-feedback-state_id}}',
            '{{%feedback}}',
            'state_id',
            '{{%feedback_state}}',
            'id',
            'CASCADE'
        );


        // creates index for column `describe_id`
        $this->createIndex(
            '{{%idx-feedback-describe_id}}',
            '{{%feedback}}',
            'describe_id'
        );

        // add foreign key for table `{{%feedback_state}}`
        $this->addForeignKey(
            '{{%fk-feedback-describe_id}}',
            '{{%feedback}}',
            'describe_id',
            '{{%feedback_describe}}',
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
            '{{%fk-feedback-reporter}}',
            '{{%feedback}}'
        );

        // drops index for column `reporter`
        $this->dropIndex(
            '{{%idx-feedback-reporter}}',
            '{{%feedback}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-feedback-repairer}}',
            '{{%feedback}}'
        );

        // drops index for column `repairer`
        $this->dropIndex(
            '{{%idx-feedback-repairer}}',
            '{{%feedback}}'
        );

		
        // drops foreign key for table `{{%feedback_state}}`
        $this->dropForeignKey(
            '{{%fk-feedback-state_id}}',
            '{{%feedback}}'
        );

        // drops index for column `describe_id`
        $this->dropIndex(
            '{{%idx-feedback-state_id}}',
            '{{%feedback}}'
        );



        // drops foreign key for table `{{%feedback_state}}`
        $this->dropForeignKey(
            '{{%fk-feedback-describe_id}}',
            '{{%feedback}}'
        );

        // drops index for column `describe_id`
        $this->dropIndex(
            '{{%idx-feedback-describe_id}}',
            '{{%feedback}}'
        );

        $this->dropTable('{{%feedback}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%edu_student}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%edu_class}}`
 */
class m251126_040254_create_edu_student_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

         if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%edu_student}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'class_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-edu_student-user_id}}',
            '{{%edu_student}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-edu_student-user_id}}',
            '{{%edu_student}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `class_id`
        $this->createIndex(
            '{{%idx-edu_student-class_id}}',
            '{{%edu_student}}',
            'class_id'
        );

        // add foreign key for table `{{%edu_class}}`
        $this->addForeignKey(
            '{{%fk-edu_student-class_id}}',
            '{{%edu_student}}',
            'class_id',
            '{{%edu_class}}',
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
            '{{%fk-edu_student-user_id}}',
            '{{%edu_student}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-edu_student-user_id}}',
            '{{%edu_student}}'
        );

        // drops foreign key for table `{{%edu_class}}`
        $this->dropForeignKey(
            '{{%fk-edu_student-class_id}}',
            '{{%edu_student}}'
        );

        // drops index for column `class_id`
        $this->dropIndex(
            '{{%idx-edu_student-class_id}}',
            '{{%edu_student}}'
        );

        $this->dropTable('{{%edu_student}}');
    }
}

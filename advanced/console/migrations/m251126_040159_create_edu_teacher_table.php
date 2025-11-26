<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%edu_teacher}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%edu_class}}`
 */
class m251126_040159_create_edu_teacher_table extends Migration
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
        $this->createTable('{{%edu_teacher}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'class_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-edu_teacher-user_id}}',
            '{{%edu_teacher}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-edu_teacher-user_id}}',
            '{{%edu_teacher}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `class_id`
        $this->createIndex(
            '{{%idx-edu_teacher-class_id}}',
            '{{%edu_teacher}}',
            'class_id'
        );

        // add foreign key for table `{{%edu_class}}`
        $this->addForeignKey(
            '{{%fk-edu_teacher-class_id}}',
            '{{%edu_teacher}}',
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
            '{{%fk-edu_teacher-user_id}}',
            '{{%edu_teacher}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-edu_teacher-user_id}}',
            '{{%edu_teacher}}'
        );

        // drops foreign key for table `{{%edu_class}}`
        $this->dropForeignKey(
            '{{%fk-edu_teacher-class_id}}',
            '{{%edu_teacher}}'
        );

        // drops index for column `class_id`
        $this->dropIndex(
            '{{%idx-edu_teacher-class_id}}',
            '{{%edu_teacher}}'
        );

        $this->dropTable('{{%edu_teacher}}');
    }
}

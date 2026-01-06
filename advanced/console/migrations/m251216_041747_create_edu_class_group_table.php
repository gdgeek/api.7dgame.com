<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%edu_class_group}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%edu_class}}`
 * - `{{%group}}`
 */
class m251216_041747_create_edu_class_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%edu_class_group}}', [
            'id' => $this->primaryKey(),
            'class_id' => $this->integer()->notNull(),
            'group_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `class_id`
        $this->createIndex(
            '{{%idx-edu_class_group-class_id}}',
            '{{%edu_class_group}}',
            'class_id'
        );

        // add foreign key for table `{{%edu_class}}`
        $this->addForeignKey(
            '{{%fk-edu_class_group-class_id}}',
            '{{%edu_class_group}}',
            'class_id',
            '{{%edu_class}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_id`
        $this->createIndex(
            '{{%idx-edu_class_group-group_id}}',
            '{{%edu_class_group}}',
            'group_id'
        );

        // add foreign key for table `{{%group}}`
        $this->addForeignKey(
            '{{%fk-edu_class_group-group_id}}',
            '{{%edu_class_group}}',
            'group_id',
            '{{%group}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%edu_class}}`
        $this->dropForeignKey(
            '{{%fk-edu_class_group-class_id}}',
            '{{%edu_class_group}}'
        );

        // drops index for column `class_id`
        $this->dropIndex(
            '{{%idx-edu_class_group-class_id}}',
            '{{%edu_class_group}}'
        );

        // drops foreign key for table `{{%group}}`
        $this->dropForeignKey(
            '{{%fk-edu_class_group-group_id}}',
            '{{%edu_class_group}}'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            '{{%idx-edu_class_group-group_id}}',
            '{{%edu_class_group}}'
        );

        $this->dropTable('{{%edu_class_group}}');
    }
}

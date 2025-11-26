<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%edu_class}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%edu_school}}`
 * - `{{%file}}`
 */
class m251126_034617_create_edu_class_table extends Migration
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
        $this->createTable('{{%edu_class}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'school_id' => $this->integer(),
            'image_id' => $this->integer(),
            'info' => $this->json(),
        ],$tableOptions);

        // creates index for column `school_id`
        $this->createIndex(
            '{{%idx-edu_class-school_id}}',
            '{{%edu_class}}',
            'school_id'
        );

        // add foreign key for table `{{%edu_school}}`
        $this->addForeignKey(
            '{{%fk-edu_class-school_id}}',
            '{{%edu_class}}',
            'school_id',
            '{{%edu_school}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-edu_class-image_id}}',
            '{{%edu_class}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-edu_class-image_id}}',
            '{{%edu_class}}',
            'image_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%edu_school}}`
        $this->dropForeignKey(
            '{{%fk-edu_class-school_id}}',
            '{{%edu_class}}'
        );

        // drops index for column `school_id`
        $this->dropIndex(
            '{{%idx-edu_class-school_id}}',
            '{{%edu_class}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-edu_class-image_id}}',
            '{{%edu_class}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-edu_class-image_id}}',
            '{{%edu_class}}'
        );

        $this->dropTable('{{%edu_class}}');
    }
}

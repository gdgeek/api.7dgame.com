<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%edu_school}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 * - `{{%user}}`
 */

//php yii migrate/create create_edu_class_table --fields="name:string,created_at:dateTime:notNull,updated_at:dateTime:notNull,school_id:integer:foreignKey(edu_school),image_id:integer:foreignKey(file),info:json"
class m251126_032535_create_edu_school_table extends Migration
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
        $this->createTable('{{%edu_school}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'image_id' => $this->integer(),
            'info' => $this->json(),
            'principal_id' => $this->integer(),
        ],$tableOptions);

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-edu_school-image_id}}',
            '{{%edu_school}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-edu_school-image_id}}',
            '{{%edu_school}}',
            'image_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `principal_id`
        $this->createIndex(
            '{{%idx-edu_school-principal_id}}',
            '{{%edu_school}}',
            'principal_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-edu_school-principal_id}}',
            '{{%edu_school}}',
            'principal_id',
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
        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-edu_school-image_id}}',
            '{{%edu_school}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-edu_school-image_id}}',
            '{{%edu_school}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-edu_school-principal_id}}',
            '{{%edu_school}}'
        );

        // drops index for column `principal_id`
        $this->dropIndex(
            '{{%idx-edu_school-principal_id}}',
            '{{%edu_school}}'
        );

        $this->dropTable('{{%edu_school}}');
    }
}

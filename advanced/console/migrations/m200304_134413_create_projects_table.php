<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%projects}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m200304_134413_create_projects_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%projects}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer(),
            'title' => $this->string(),
            'information' => $this->text(),
            'configure' => $this->text(),
            'logic' => $this->text(),
        ], $tableOptions);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-projects-author_id}}',
            '{{%projects}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-projects-author_id}}',
            '{{%projects}}',
            'author_id',
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
            '{{%fk-projects-author_id}}',
            '{{%projects}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-projects-author_id}}',
            '{{%projects}}'
        );

        $this->dropTable('{{%projects}}');
    }
}

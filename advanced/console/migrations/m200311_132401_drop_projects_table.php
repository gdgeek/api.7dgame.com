<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%projects}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m200311_132401_drop_projects_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createTable('{{%projects}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer(),
            'title' => $this->string(),
            'information' => $this->text(),
            'configure' => $this->text(),
            'logic' => $this->text(),
        ]);

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
}

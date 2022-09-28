<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%programme}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m200311_132242_create_programme_table extends Migration
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
        $this->createTable('{{%programme}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer(),
            'title' => $this->string(),
            'information' => $this->text(),
            'configure' => $this->text(),
            'logic' => $this->text(),
        ],$tableOptions);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-programme-author_id}}',
            '{{%programme}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-programme-author_id}}',
            '{{%programme}}',
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
            '{{%fk-programme-author_id}}',
            '{{%programme}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-programme-author_id}}',
            '{{%programme}}'
        );

        $this->dropTable('{{%programme}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bilibili}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%file}}`
 */
class m231116_101715_create_bilibili_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bilibili}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime(),
            'title' => $this->text(),
            'image_id' => $this->integer(),
            'description' => $this->text(),
        ]);

        // creates index for column `author_id`
        $this->createIndex(
            '{{%idx-bilibili-author_id}}',
            '{{%bilibili}}',
            'author_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-bilibili-author_id}}',
            '{{%bilibili}}',
            'author_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-bilibili-image_id}}',
            '{{%bilibili}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-bilibili-image_id}}',
            '{{%bilibili}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-bilibili-author_id}}',
            '{{%bilibili}}'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            '{{%idx-bilibili-author_id}}',
            '{{%bilibili}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-bilibili-image_id}}',
            '{{%bilibili}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-bilibili-image_id}}',
            '{{%bilibili}}'
        );

        $this->dropTable('{{%bilibili}}');
    }
}

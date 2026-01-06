<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%file}}`
 * - `{{%user}}`
 */
class m251216_034117_create_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'image_id' => $this->integer(),
            'user_id' => $this->integer()->notNull(),
            'info' => $this->json(),
        ]);

        // creates index for column `image_id`
        $this->createIndex(
            '{{%idx-group-image_id}}',
            '{{%group}}',
            'image_id'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-group-image_id}}',
            '{{%group}}',
            'image_id',
            '{{%file}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-group-user_id}}',
            '{{%group}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-group-user_id}}',
            '{{%group}}',
            'user_id',
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
            '{{%fk-group-image_id}}',
            '{{%group}}'
        );

        // drops index for column `image_id`
        $this->dropIndex(
            '{{%idx-group-image_id}}',
            '{{%group}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-group-user_id}}',
            '{{%group}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-group-user_id}}',
            '{{%group}}'
        );

        $this->dropTable('{{%group}}');
    }
}

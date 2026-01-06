<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_user}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%group}}`
 */
class m251216_035738_create_group_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_user}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'group_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-group_user-user_id}}',
            '{{%group_user}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-group_user-user_id}}',
            '{{%group_user}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_id`
        $this->createIndex(
            '{{%idx-group_user-group_id}}',
            '{{%group_user}}',
            'group_id'
        );

        // add foreign key for table `{{%group}}`
        $this->addForeignKey(
            '{{%fk-group_user-group_id}}',
            '{{%group_user}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-group_user-user_id}}',
            '{{%group_user}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-group_user-user_id}}',
            '{{%group_user}}'
        );

        // drops foreign key for table `{{%group}}`
        $this->dropForeignKey(
            '{{%fk-group_user-group_id}}',
            '{{%group_user}}'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            '{{%idx-group_user-group_id}}',
            '{{%group_user}}'
        );

        $this->dropTable('{{%group_user}}');
    }
}

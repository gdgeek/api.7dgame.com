<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_info}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%file}}`
 */
class m220314_024610_create_user_info_table extends Migration
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

        $this->createTable('{{%user_info}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'avatar' => $this->integer(),
            'info' => $this->json(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-user_info-user_id}}',
            '{{%user_info}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_info-user_id}}',
            '{{%user_info}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `avatar`
        $this->createIndex(
            '{{%idx-user_info-avatar}}',
            '{{%user_info}}',
            'avatar'
        );

        // add foreign key for table `{{%file}}`
        $this->addForeignKey(
            '{{%fk-user_info-avatar}}',
            '{{%user_info}}',
            'avatar',
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
            '{{%fk-user_info-user_id}}',
            '{{%user_info}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-user_info-user_id}}',
            '{{%user_info}}'
        );

        // drops foreign key for table `{{%file}}`
        $this->dropForeignKey(
            '{{%fk-user_info-avatar}}',
            '{{%user_info}}'
        );

        // drops index for column `avatar`
        $this->dropIndex(
            '{{%idx-user_info-avatar}}',
            '{{%user_info}}'
        );

        $this->dropTable('{{%user_info}}');
    }
}

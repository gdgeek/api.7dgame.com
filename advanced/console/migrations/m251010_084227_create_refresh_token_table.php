<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%refresh_token}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m251010_084227_create_refresh_token_table extends Migration
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
        $this->createTable('{{%refresh_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'key' => $this->string()->notNull(),
            'created_at' => $this->dateTime(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-refresh_token-user_id}}',
            '{{%refresh_token}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-refresh_token-user_id}}',
            '{{%refresh_token}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-refresh_token-user_id}}',
            '{{%refresh_token}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-refresh_token-user_id}}',
            '{{%refresh_token}}'
        );

        $this->dropTable('{{%refresh_token}}');
    }
}

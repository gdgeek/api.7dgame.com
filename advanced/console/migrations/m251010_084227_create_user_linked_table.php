<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_linked}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m251010_084227_create_user_linked_table extends Migration
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
        $this->createTable('{{%user_linked}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'key' => $this->string()->notNull(),
            'created_at' => $this->dateTime(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-user_linked-user_id}}',
            '{{%user_linked}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_linked-user_id}}',
            '{{%user_linked}}',
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
            '{{%fk-user_linked-user_id}}',
            '{{%user_linked}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-user_linked-user_id}}',
            '{{%user_linked}}'
        );

        $this->dropTable('{{%user_linked}}');
    }
}

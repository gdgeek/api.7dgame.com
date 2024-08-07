<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apple_id}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m240807_024940_create_apple_id_table extends Migration
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
        $this->createTable('{{%apple_id}}', [
            'id' => $this->primaryKey(),
            'apple_id' => $this->string()->notNull(),
            'email' => $this->string(),
            'first_name' => $this->string(),
            'last_name' => $this->string(),
            'user_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'tooken' => $this->string(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-apple_id-user_id}}',
            '{{%apple_id}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-apple_id-user_id}}',
            '{{%apple_id}}',
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
            '{{%fk-apple_id-user_id}}',
            '{{%apple_id}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-apple_id-user_id}}',
            '{{%apple_id}}'
        );

        $this->dropTable('{{%apple_id}}');
    }
}

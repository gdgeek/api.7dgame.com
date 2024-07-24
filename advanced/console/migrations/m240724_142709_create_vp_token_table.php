<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vp_token}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m240724_142709_create_vp_token_table extends Migration
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

        $this->createTable('{{%vp_token}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull()->unique(),
            'token' => $this->string()->notNull()->unique(), 
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp(),
            'user_id' => $this->integer(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-vp_token-user_id}}',
            '{{%vp_token}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-vp_token-user_id}}',
            '{{%vp_token}}',
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
            '{{%fk-vp_token-user_id}}',
            '{{%vp_token}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-vp_token-user_id}}',
            '{{%vp_token}}'
        );

        $this->dropTable('{{%vp_token}}');
    }
}

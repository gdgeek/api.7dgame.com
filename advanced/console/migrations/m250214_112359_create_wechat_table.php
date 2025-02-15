<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wechat}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m250214_112359_create_wechat_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wechat}}', [
            'id' => $this->primaryKey(),
            'openid' => $this->string()->unique()->notNull(),
            'user_id' => $this->integer(),
            'created_at' => $this->dateTime(),
           // 'info' => $this->json(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-wechat-user_id}}',
            '{{%wechat}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-wechat-user_id}}',
            '{{%wechat}}',
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
            '{{%fk-wechat-user_id}}',
            '{{%wechat}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-wechat-user_id}}',
            '{{%wechat}}'
        );

        $this->dropTable('{{%wechat}}');
    }
}

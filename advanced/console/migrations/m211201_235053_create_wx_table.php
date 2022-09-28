<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wx}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m211201_235053_create_wx_table extends Migration
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

        $this->createTable('{{%wx}}', [
            'id' => $this->primaryKey(),
            'wx_openid' => $this->string(),
            'token' => $this->string(),
            'user_id' => $this->integer(),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-wx-user_id}}',
            '{{%wx}}',
            'user_id'
        );

// creates index for column `token`
        $this->createIndex(
            '{{%idx-wx-token}}',
            '{{%wx}}',
            'token'
        );

// creates index for column `wx_openid`
        $this->createIndex(
            '{{%idx-wx-wx_openid}}',
            '{{%wx}}',
            'wx_openid'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-wx-user_id}}',
            '{{%wx}}',
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
            '{{%fk-wx-user_id}}',
            '{{%wx}}'
        );

        $this->dropIndex(
            '{{%idx-wx-user_id}}',
            '{{%wx}}'
        );

        $this->dropIndex(
            '{{%idx-wx-wx_openid}}',
            '{{%wx}}'
        );

        $this->dropIndex(
            '{{%idx-wx-token}}',
            '{{%wx}}'
        );

        $this->dropTable('{{%wx}}');
    }
}

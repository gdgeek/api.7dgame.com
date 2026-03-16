<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invitation_record}}`.
 * 记录邀请链接注册的用户关系：谁邀请了谁。
 */
class m260312_220000_create_invitation_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invitation_record}}', [
            'id' => $this->primaryKey(),
            'invite_code' => $this->string(64)->notNull()->comment('邀请码'),
            'inviter_id' => $this->integer()->notNull()->comment('邀请人ID'),
            'invitee_id' => $this->integer()->notNull()->comment('被邀请人ID'),
            'created_at' => $this->integer()->notNull()->comment('注册时间'),
        ]);

        // 邀请人外键 → user.id，级联删除
        $this->addForeignKey(
            'fk_invitation_record_inviter',
            '{{%invitation_record}}',
            'inviter_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // 被邀请人外键 → user.id，级联删除
        $this->addForeignKey(
            'fk_invitation_record_invitee',
            '{{%invitation_record}}',
            'invitee_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // 邀请码索引，方便按邀请码查询记录
        $this->createIndex(
            'idx_invitation_record_invite_code',
            '{{%invitation_record}}',
            'invite_code'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_invitation_record_invitee', '{{%invitation_record}}');
        $this->dropForeignKey('fk_invitation_record_inviter', '{{%invitation_record}}');
        $this->dropTable('{{%invitation_record}}');
    }
}

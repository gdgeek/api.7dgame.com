<?php

use yii\db\Migration;

/**
 * Handles adding email_verified_at column to table `{{%user}}`.
 * 
 * This migration adds the email_verified_at field to track when a user's email
 * address was verified. This is required for the email verification and password
 * reset functionality.
 */
class m260121_000000_add_email_verified_at_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'email_verified_at', $this->integer()->null()->comment('邮箱验证时间戳'));
        
        // Add index for faster queries on email verification status
        $this->createIndex(
            'idx-user-email_verified_at',
            '{{%user}}',
            'email_verified_at'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop index first
        $this->dropIndex('idx-user-email_verified_at', '{{%user}}');
        
        // Then drop column
        $this->dropColumn('{{%user}}', 'email_verified_at');
    }
}

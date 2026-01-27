<?php

use yii\db\Migration;

/**
 * Creates security-related tables for backend security hardening.
 * 
 * Tables created:
 * - token_revocation: JWT token revocation list
 * - password_history: Password history tracking
 * - failed_login_attempt: Failed login attempt tracking
 * - audit_log: Security event logging
 * - security_config: Centralized security configuration
 * 
 * Requirements: 1.4, 4.1, 8.4
 */
class m260125_000001_create_security_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Token Revocation Table - for JWT token revocation list
        // Requirement 8.4: THE Token_Manager SHALL maintain a revocation list for invalidated tokens
        $this->createTable('{{%token_revocation}}', [
            'id' => $this->primaryKey(),
            'jti' => $this->string(255)->notNull()->unique()->comment('JWT ID - unique token identifier'),
            'user_id' => $this->integer()->notNull()->comment('User ID who owns the token'),
            'revoked_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('When the token was revoked'),
            'expires_at' => $this->timestamp()->null()->comment('Original token expiration time'),
            'reason' => $this->string(255)->null()->comment('Reason for revocation'),
        ]);

        $this->createIndex('idx_token_revocation_jti', '{{%token_revocation}}', 'jti');
        $this->createIndex('idx_token_revocation_user_id', '{{%token_revocation}}', 'user_id');
        $this->createIndex('idx_token_revocation_expires_at', '{{%token_revocation}}', 'expires_at');

        // Password History Table - for password history tracking
        // Requirement 5.3: WHEN a password is changed, THE Auth_Manager SHALL verify it differs from the previous 5 passwords
        $this->createTable('{{%password_history}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('User ID'),
            'password_hash' => $this->string(255)->notNull()->comment('Hashed password'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('When the password was created'),
        ]);

        $this->createIndex('idx_password_history_user_id', '{{%password_history}}', 'user_id');

        // Failed Login Attempt Table - for tracking failed logins
        // Requirement 3.8: THE Auth_Manager SHALL implement account lockout after 5 failed login attempts within 15 minutes
        // Requirement 3.9: WHEN a failed login occurs, THE Audit_Logger SHALL record the attempt with IP address and timestamp
        $this->createTable('{{%failed_login_attempt}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull()->comment('Attempted username'),
            'ip_address' => $this->string(45)->notNull()->comment('IP address of the attempt (supports IPv6)'),
            'attempted_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('When the attempt occurred'),
            'user_agent' => $this->text()->null()->comment('User agent string'),
        ]);

        $this->createIndex('idx_failed_login_attempt_username', '{{%failed_login_attempt}}', 'username');
        $this->createIndex('idx_failed_login_attempt_ip_address', '{{%failed_login_attempt}}', 'ip_address');
        $this->createIndex('idx_failed_login_attempt_attempted_at', '{{%failed_login_attempt}}', 'attempted_at');

        // Audit Log Table - for security event logging
        // Requirement 9.4: WHEN security events occur, THE Audit_Logger SHALL record them with timestamp, user, IP address, and action
        $this->createTable('{{%audit_log}}', [
            'id' => $this->primaryKey(),
            'event_type' => $this->string(50)->notNull()->comment('Type of security event'),
            'user_id' => $this->integer()->null()->comment('User ID (null for anonymous events)'),
            'ip_address' => $this->string(45)->null()->comment('IP address (supports IPv6)'),
            'action' => $this->string(100)->notNull()->comment('Action performed'),
            'resource' => $this->string(255)->null()->comment('Resource affected'),
            'context' => $this->json()->null()->comment('Additional context data'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('When the event occurred'),
        ]);

        $this->createIndex('idx_audit_log_event_type', '{{%audit_log}}', 'event_type');
        $this->createIndex('idx_audit_log_user_id', '{{%audit_log}}', 'user_id');
        $this->createIndex('idx_audit_log_created_at', '{{%audit_log}}', 'created_at');

        // Security Config Table - for centralized security configuration
        // Requirement 11.1: THE Security_Hardening_System SHALL maintain a centralized security configuration file
        $this->createTable('{{%security_config}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(255)->notNull()->unique()->comment('Configuration key'),
            'value' => $this->text()->notNull()->comment('Configuration value'),
            'updated_by' => $this->integer()->null()->comment('User ID who last updated'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP')->comment('Last update time'),
        ]);

        $this->createIndex('idx_security_config_key', '{{%security_config}}', 'key');

        // Insert default security configuration values
        $this->batchInsert('{{%security_config}}', ['key', 'value'], [
            // Rate limiting configuration
            ['rate_limit_ip_requests_per_minute', '100'],
            ['rate_limit_user_requests_per_hour', '1000'],
            ['rate_limit_login_attempts_per_15min', '5'],
            
            // Password policy configuration
            ['password_min_length', '12'],
            ['password_require_uppercase', 'true'],
            ['password_require_lowercase', 'true'],
            ['password_require_digit', 'true'],
            ['password_require_special', 'true'],
            ['password_history_count', '5'],
            ['password_expiry_days_admin', '90'],
            
            // JWT configuration
            ['jwt_access_token_expiry_seconds', '3600'],
            ['jwt_refresh_token_expiry_seconds', '604800'],
            
            // Account lockout configuration
            ['account_lockout_threshold', '5'],
            ['account_lockout_duration_minutes', '30'],
            ['account_lockout_window_minutes', '15'],
            
            // Session configuration
            ['session_timeout_minutes', '30'],
            
            // File upload configuration
            ['upload_max_file_size_bytes', '10485760'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%security_config}}');
        $this->dropTable('{{%audit_log}}');
        $this->dropTable('{{%failed_login_attempt}}');
        $this->dropTable('{{%password_history}}');
        $this->dropTable('{{%token_revocation}}');
    }
}

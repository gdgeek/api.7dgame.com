<?php
/**
 * Security Configuration File
 * 
 * This file contains centralized security configuration for the application.
 * All security-related settings should be defined here for consistency and auditability.
 * 
 * Requirements: 1.4, 4.1, 8.4, 11.1
 * 
 * IMPORTANT: Sensitive values should be loaded from environment variables.
 * Never commit actual secrets to version control.
 */

return [
    /**
     * JWT (JSON Web Token) Configuration
     * Requirement 3.1: WHEN a JWT token is generated, THE Token_Manager SHALL use a cryptographically secure secret key of at least 256 bits
     * Requirement 3.2: WHEN a JWT token is generated, THE Token_Manager SHALL include an expiration time of no more than 1 hour
     */
    'jwt' => [
        // JWT secret key - MUST be loaded from environment variable
        // Minimum 256 bits (32 bytes) for HS256 algorithm
        'secret' => getenv('JWT_SECRET') ?: null,
        
        // Algorithm for JWT signing
        'algorithm' => 'HS256',
        
        // Access token expiration time in seconds (1 hour)
        'accessTokenExpiry' => (int)(getenv('JWT_ACCESS_TOKEN_EXPIRY') ?: 3600),
        
        // Refresh token expiration time in seconds (7 days)
        'refreshTokenExpiry' => (int)(getenv('JWT_REFRESH_TOKEN_EXPIRY') ?: 604800),
        
        // Issuer claim for JWT
        'issuer' => getenv('JWT_ISSUER') ?: 'yii2-app',
        
        // Previous secret for key rotation (allows validation of tokens signed with old key)
        'previousSecret' => getenv('JWT_PREVIOUS_SECRET') ?: null,
        
        // Grace period for old key validation in seconds (24 hours)
        'keyRotationGracePeriod' => (int)(getenv('JWT_KEY_ROTATION_GRACE_PERIOD') ?: 86400),
    ],

    /**
     * Rate Limiting Configuration
     * Requirement 4.1: WHEN API requests are received, THE Rate_Limiter SHALL enforce a limit of 100 requests per minute per IP address
     * Requirement 4.2: WHEN API requests are received, THE Rate_Limiter SHALL enforce a limit of 1000 requests per hour per authenticated user
     */
    'rateLimit' => [
        // Requests per minute per IP address
        'ipRequestsPerMinute' => (int)(getenv('RATE_LIMIT_IP_PER_MINUTE') ?: 100),
        
        // Requests per hour per authenticated user
        'userRequestsPerHour' => (int)(getenv('RATE_LIMIT_USER_PER_HOUR') ?: 1000),
        
        // Login attempts per 15 minutes per IP/username
        'loginAttemptsPerWindow' => (int)(getenv('RATE_LIMIT_LOGIN_ATTEMPTS') ?: 5),
        
        // Login rate limit window in minutes
        'loginWindowMinutes' => (int)(getenv('RATE_LIMIT_LOGIN_WINDOW') ?: 15),
        
        // Enable rate limiting
        'enabled' => filter_var(getenv('RATE_LIMIT_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ],

    /**
     * Password Policy Configuration
     * Requirement 5.1: WHEN a password is created, THE Auth_Manager SHALL enforce a minimum length of 12 characters
     * Requirement 5.2: WHEN a password is created, THE Auth_Manager SHALL require at least one uppercase letter, one lowercase letter, one digit, and one special character
     * Requirement 5.3: WHEN a password is changed, THE Auth_Manager SHALL verify it differs from the previous 5 passwords
     * Requirement 5.4: WHEN a password is stored, THE Auth_Manager SHALL hash it using bcrypt with a cost factor of at least 12
     */
    'password' => [
        // Minimum password length
        'minLength' => (int)(getenv('PASSWORD_MIN_LENGTH') ?: 12),
        
        // Require uppercase letter
        'requireUppercase' => filter_var(getenv('PASSWORD_REQUIRE_UPPERCASE') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        
        // Require lowercase letter
        'requireLowercase' => filter_var(getenv('PASSWORD_REQUIRE_LOWERCASE') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        
        // Require digit
        'requireDigit' => filter_var(getenv('PASSWORD_REQUIRE_DIGIT') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        
        // Require special character
        'requireSpecial' => filter_var(getenv('PASSWORD_REQUIRE_SPECIAL') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        
        // Number of previous passwords to check against
        'historyCount' => (int)(getenv('PASSWORD_HISTORY_COUNT') ?: 5),
        
        // Password expiry for admin accounts (days)
        'adminExpiryDays' => (int)(getenv('PASSWORD_ADMIN_EXPIRY_DAYS') ?: 90),
        
        // Bcrypt cost factor (minimum 12)
        'bcryptCost' => max(12, (int)(getenv('PASSWORD_BCRYPT_COST') ?: 12)),
        
        // Path to weak password list file
        'weakPasswordListPath' => getenv('WEAK_PASSWORD_LIST_PATH') ?: __DIR__ . '/weak-passwords.txt',
    ],

    /**
     * Account Lockout Configuration
     * Requirement 3.8: THE Auth_Manager SHALL implement account lockout after 5 failed login attempts within 15 minutes
     */
    'accountLockout' => [
        // Number of failed attempts before lockout
        'threshold' => (int)(getenv('ACCOUNT_LOCKOUT_THRESHOLD') ?: 5),
        
        // Lockout duration in minutes
        'durationMinutes' => (int)(getenv('ACCOUNT_LOCKOUT_DURATION') ?: 30),
        
        // Time window for counting failed attempts (minutes)
        'windowMinutes' => (int)(getenv('ACCOUNT_LOCKOUT_WINDOW') ?: 15),
    ],

    /**
     * Session Configuration
     * Requirement 8.6: THE Auth_Manager SHALL implement session timeout after 30 minutes of inactivity
     */
    'session' => [
        // Session timeout in minutes
        'timeoutMinutes' => (int)(getenv('SESSION_TIMEOUT_MINUTES') ?: 30),
        
        // Regenerate session ID on authentication
        'regenerateOnAuth' => filter_var(getenv('SESSION_REGENERATE_ON_AUTH') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ],

    /**
     * File Upload Configuration
     * Requirement 2.3: WHEN a file is uploaded, THE Upload_Validator SHALL enforce a maximum file size limit of 10MB
     */
    'upload' => [
        // Maximum file size in bytes (10MB)
        'maxFileSize' => (int)(getenv('UPLOAD_MAX_FILE_SIZE') ?: 10485760),
        
        // Allowed MIME types
        'allowedMimeTypes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/x-rar-compressed',
        ],
        
        // Allowed file extensions
        'allowedExtensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'pdf', 'doc', 'docx',
            'zip', 'rar',
        ],
        
        // Upload directory (outside web root)
        'uploadPath' => getenv('UPLOAD_PATH') ?: '/var/uploads',
    ],

    /**
     * CORS Configuration
     * Requirement 4.4: THE CORS_Manager SHALL only allow cross-origin requests from explicitly whitelisted domains
     * Requirement 4.5: WHEN CORS is configured, THE CORS_Manager SHALL NOT use wildcard (*) for allowed origins in production
     */
    'cors' => [
        // Allowed origins (comma-separated in env var)
        'allowedOrigins' => array_filter(explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: '')),
        
        // Allowed HTTP methods
        'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        
        // Allowed headers
        'allowedHeaders' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token'],
        
        // Allow credentials
        'allowCredentials' => filter_var(getenv('CORS_ALLOW_CREDENTIALS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        
        // Preflight cache duration in seconds
        'maxAge' => (int)(getenv('CORS_MAX_AGE') ?: 3600),
    ],

    /**
     * Redis Configuration for Rate Limiting and Token Revocation
     * Requirement 4.1: Rate limiting using Redis
     * Requirement 8.4: Token revocation list using Redis
     */
    'redis' => [
        // Redis host
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        
        // Redis port
        'port' => (int)(getenv('REDIS_PORT') ?: 6379),
        
        // Redis password (optional)
        'password' => getenv('REDIS_PASSWORD') ?: null,
        
        // Redis database index for security features
        'database' => (int)(getenv('REDIS_SECURITY_DB') ?: 1),
        
        // Connection timeout in seconds
        'timeout' => (float)(getenv('REDIS_TIMEOUT') ?: 2.5),
        
        // Key prefix for security-related keys
        'keyPrefix' => getenv('REDIS_KEY_PREFIX') ?: 'security:',
        
        // Enable Redis for rate limiting
        'enableRateLimit' => filter_var(getenv('REDIS_ENABLE_RATE_LIMIT') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        
        // Enable Redis for token revocation
        'enableTokenRevocation' => filter_var(getenv('REDIS_ENABLE_TOKEN_REVOCATION') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ],

    /**
     * Audit Logging Configuration
     * Requirement 9.4: WHEN security events occur, THE Audit_Logger SHALL record them with timestamp, user, IP address, and action
     * Requirement 9.5: THE Audit_Logger SHALL implement log rotation and retention policies
     */
    'audit' => [
        // Enable audit logging
        'enabled' => filter_var(getenv('AUDIT_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        
        // Log retention period in days
        'retentionDays' => (int)(getenv('AUDIT_RETENTION_DAYS') ?: 90),
        
        // Log file path
        'logPath' => getenv('AUDIT_LOG_PATH') ?: '@runtime/logs/security',
        
        // Events to log
        'logEvents' => [
            'authentication',
            'authorization',
            'file_upload',
            'data_access',
            'config_change',
            'security_event',
        ],
    ],

    /**
     * Security Headers Configuration
     * Requirement 10.3: THE Security_Hardening_System SHALL implement Content Security Policy headers
     * Requirement 10.4: THE Security_Hardening_System SHALL set X-XSS-Protection header to "1; mode=block"
     * Requirement 10.5: THE Security_Hardening_System SHALL set X-Content-Type-Options header to "nosniff"
     * Requirement 11.4: THE Security_Hardening_System SHALL implement security headers including HSTS, CSP, and X-Frame-Options
     */
    'headers' => [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'",
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /**
     * Error Handling Configuration
     * Requirement 9.1: WHEN errors occur in production, THE Error_Handler SHALL return generic error messages to clients
     * Requirement 9.3: THE Error_Handler SHALL NOT include stack traces, file paths, or database queries in client responses
     */
    'errorHandling' => [
        // Show detailed errors (should be false in production)
        'showDetailedErrors' => filter_var(getenv('SHOW_DETAILED_ERRORS') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        
        // Sensitive patterns to filter from logs
        'sensitivePatterns' => [
            '/password/i',
            '/passwd/i',
            '/pwd/i',
            '/token/i',
            '/access_token/i',
            '/refresh_token/i',
            '/api_key/i',
            '/secret/i',
            '/card_number/i',
            '/cvv/i',
            '/ccv/i',
            '/ssn/i',
            '/passport/i',
        ],
        
        // Replacement text for filtered sensitive data
        'sensitiveReplacement' => '[FILTERED]',
        
        // Send alerts for critical errors
        'alertOnCritical' => filter_var(getenv('ALERT_ON_CRITICAL_ERRORS') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        
        // Alert email addresses (comma-separated)
        'alertEmails' => array_filter(explode(',', getenv('ALERT_EMAILS') ?: '')),
    ],

    /**
     * Environment Detection
     */
    'environment' => [
        // Current environment (development, staging, production)
        'current' => getenv('APP_ENV') ?: 'production',
        
        // Is production environment
        'isProduction' => (getenv('APP_ENV') ?: 'production') === 'production',
        
        // Debug mode (should be false in production)
        'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ],
];

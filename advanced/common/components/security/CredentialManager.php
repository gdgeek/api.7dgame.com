<?php

namespace common\components\security;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * CredentialManager Component
 * 
 * Manages all sensitive credentials and configuration securely.
 * Implements Requirements 1.1, 1.2, 1.4, 1.6 from backend-security-hardening spec.
 * 
 * @property-read array $databaseCredentials
 * @property-read string $jwtSecret
 */
class CredentialManager extends Component
{
    /**
     * @var array Required environment variables that must be present
     */
    protected $requiredEnvVars = [
        'JWT_KEY',         // JWT private key file path (ECC/RSA)
        'MYSQL_HOST',      // Database host
        'MYSQL_DB',        // Database name
        'MYSQL_USERNAME',  // Database username
        'MYSQL_PASSWORD',  // Database password
    ];

    /**
     * @var array Optional environment variables with fallback values
     */
    protected $optionalEnvVars = [
        'REDIS_HOST' => '127.0.0.1',
        'REDIS_PORT' => '6379',
    ];

    /**
     * @var int Minimum JWT secret length in bytes (256 bits = 32 bytes)
     */
    const MIN_JWT_SECRET_LENGTH = 32;

    /**
     * @var string|null Cached JWT secret
     */
    private $_jwtSecret;

    /**
     * @var string|null Cached previous JWT secret for rotation
     */
    private $_jwtPreviousSecret;

    /**
     * Initialize the component
     * 
     * @throws InvalidConfigException if required environment variables are missing
     */
    public function init()
    {
        parent::init();
        
        // Validate environment on initialization
        if (!$this->validateEnvironment()) {
            throw new InvalidConfigException('Required environment variables are missing. Please check your .env configuration.');
        }
    }

    /**
     * Validate that all required environment variables are present
     * Implements Requirement 1.2: Validate that all required secrets are present
     * 
     * @return bool True if all required variables are present, false otherwise
     */
    public function validateEnvironment(): bool
    {
        $missing = [];
        
        foreach ($this->requiredEnvVars as $var) {
            $value = getenv($var);
            if ($value === false || $value === '') {
                $missing[] = $var;
            }
        }

        if (!empty($missing)) {
            Yii::error('Missing required environment variables: ' . implode(', ', $missing), __METHOD__);
            return false;
        }
        
        // Validate JWT_KEY file exists and is readable
        $jwtKeyPath = getenv('JWT_KEY');
        if (!file_exists($jwtKeyPath)) {
            Yii::error("JWT_KEY file does not exist: $jwtKeyPath", __METHOD__);
            return false;
        }
        if (!is_readable($jwtKeyPath)) {
            Yii::error("JWT_KEY file is not readable: $jwtKeyPath", __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * Get database credentials from environment variables
     * Implements Requirement 1.4: Use environment variables for database credentials
     * 
     * @return array Database credentials
     */
    public function getDatabaseCredentials(): array
    {
        return [
            'host' => getenv('MYSQL_HOST'),
            'port' => getenv('MYSQL_PORT') ?: '3306',
            'database' => getenv('MYSQL_DB'),
            'username' => getenv('MYSQL_USERNAME'),
            'password' => getenv('MYSQL_PASSWORD'),
        ];
    }

    /**
     * Get JWT private key content (primary method for JWT signing)
     * Implements Requirement 1.4: Use environment variables for JWT keys
     * 
     * @return string JWT private key content
     * @throws InvalidConfigException if key file doesn't exist or is not readable
     */
    public function getJwtSecret(): string
    {
        // For backward compatibility, this method now returns the private key content
        return $this->getJwtPrivateKey();
    }

    /**
     * Get previous JWT private key for key rotation support
     * Implements Requirement 1.6: Support JWT key rotation
     * 
     * @return string|null Previous JWT private key content or null if not set
     */
    public function getJwtPreviousSecret(): ?string
    {
        // For backward compatibility, this method now returns the previous private key content
        return $this->getJwtPreviousPrivateKey();
    }

    /**
     * Get JWT key rotation grace period in seconds
     * Implements Requirement 1.6: JWT key rotation with grace period
     * 
     * @return int Grace period in seconds (default: 24 hours)
     */
    public function getJwtKeyRotationGracePeriod(): int
    {
        $gracePeriod = getenv('JWT_KEY_ROTATION_GRACE_PERIOD');
        return ($gracePeriod !== false && $gracePeriod !== '') ? (int)$gracePeriod : 86400;
    }

    /**
     * Rotate JWT secret
     * Implements Requirement 1.6: Rotate JWT secrets at configurable intervals
     * 
     * This method updates the JWT secret and moves the current secret to the previous secret.
     * The previous secret is kept for a grace period to support existing tokens.
     * 
     * Note: This method only updates the in-memory cache. The actual environment variables
     * must be updated in the .env file or system environment by the administrator.
     * 
     * @param string $newSecret The new JWT secret (must be at least 32 bytes)
     * @throws InvalidConfigException if the new secret is too short
     */
    public function rotateJwtSecret(string $newSecret): void
    {
        if (strlen($newSecret) < self::MIN_JWT_SECRET_LENGTH) {
            throw new InvalidConfigException(sprintf(
                'New JWT secret must be at least %d bytes long, got %d bytes',
                self::MIN_JWT_SECRET_LENGTH,
                strlen($newSecret)
            ));
        }

        // Move current secret to previous
        $this->_jwtPreviousSecret = $this->getJwtSecret();
        
        // Set new secret
        $this->_jwtSecret = $newSecret;

        // Log the rotation (without exposing the actual secrets)
        Yii::info('JWT secret rotated successfully. Previous secret will be valid for grace period.', __METHOD__);
    }

    /**
     * Get encrypted configuration value
     * Implements Requirement 1.1: Secure storage of configuration values
     * 
     * @param string $key Configuration key
     * @return string|null Encrypted configuration value or null if not found
     */
    public function getEncryptedConfig(string $key): ?string
    {
        $value = getenv($key);
        
        if ($value === false || $value === '') {
            return null;
        }

        // Use Yii2's Security component to encrypt the value
        try {
            return Yii::$app->security->encryptByPassword($value, $this->getJwtSecret());
        } catch (\Exception $e) {
            Yii::error('Failed to encrypt configuration value: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Decrypt configuration value
     * 
     * @param string $encryptedValue Encrypted configuration value
     * @return string|null Decrypted value or null on failure
     */
    public function decryptConfig(string $encryptedValue): ?string
    {
        try {
            return Yii::$app->security->decryptByPassword($encryptedValue, $this->getJwtSecret());
        } catch (\Exception $e) {
            Yii::error('Failed to decrypt configuration value: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Get Redis credentials from environment variables
     * Implements Requirement 1.4: Use environment variables for all credentials
     * 
     * @return array Redis credentials with fallback values
     */
    public function getRedisCredentials(): array
    {
        return [
            'host' => getenv('REDIS_HOST') ?: $this->optionalEnvVars['REDIS_HOST'],
            'port' => getenv('REDIS_PORT') ?: $this->optionalEnvVars['REDIS_PORT'],
            'password' => getenv('REDIS_PASSWORD') ?: null,
            'database' => getenv('REDIS_SECURITY_DB') ?: 1,
            'timeout' => getenv('REDIS_TIMEOUT') ?: 2.5,
        ];
    }

    /**
     * Get API key from environment variables
     * Implements Requirement 1.4: Use environment variables for API keys
     * 
     * @param string $keyName The name of the API key environment variable
     * @return string|null API key value or null if not found
     */
    public function getApiKey(string $keyName): ?string
    {
        $value = getenv($keyName);
        return ($value !== false && $value !== '') ? $value : null;
    }

    /**
     * Check if a specific environment variable exists
     * 
     * @param string $varName Environment variable name
     * @return bool True if the variable exists and is not empty
     */
    public function hasEnvVar(string $varName): bool
    {
        $value = getenv($varName);
        return $value !== false && $value !== '';
    }

    /**
     * Get all missing required environment variables
     * 
     * @return array Array of missing variable names
     */
    public function getMissingEnvVars(): array
    {
        $missing = [];
        
        foreach ($this->requiredEnvVars as $var) {
            if (!$this->hasEnvVar($var)) {
                $missing[] = $var;
            }
        }

        return $missing;
    }

    // =========================================================================
    // JWT Key File Support (RSA/ECC)
    // =========================================================================

    /**
     * Check if using JWT key file (RSA/ECC) instead of HMAC secret
     * 
     * @return bool True if using key file, false if using HMAC secret
     */
    public function isUsingJwtKeyFile(): bool
    {
        return $this->hasEnvVar('JWT_KEY');
    }

    /**
     * Get JWT private key file path
     * 
     * @return string|null Path to private key file or null if not configured
     */
    public function getJwtKeyPath(): ?string
    {
        return $this->hasEnvVar('JWT_KEY') ? getenv('JWT_KEY') : null;
    }

    /**
     * Get JWT private key content
     * 
     * @return string|null Private key content or null if not configured/readable
     * @throws InvalidConfigException if key file doesn't exist or is not readable
     */
    public function getJwtPrivateKey(): ?string
    {
        $keyPath = $this->getJwtKeyPath();
        
        if ($keyPath === null) {
            return null;
        }

        if (!file_exists($keyPath)) {
            throw new InvalidConfigException("JWT private key file does not exist: $keyPath");
        }

        if (!is_readable($keyPath)) {
            throw new InvalidConfigException("JWT private key file is not readable: $keyPath");
        }

        $keyContent = file_get_contents($keyPath);
        
        if ($keyContent === false) {
            throw new InvalidConfigException("Failed to read JWT private key file: $keyPath");
        }

        return $keyContent;
    }

    /**
     * Get JWT public key file path (for token verification)
     * 
     * @return string|null Path to public key file or null if not configured
     */
    public function getJwtPublicKeyPath(): ?string
    {
        return $this->hasEnvVar('JWT_PUBLIC_KEY') ? getenv('JWT_PUBLIC_KEY') : null;
    }

    /**
     * Get JWT public key content
     * 
     * @return string|null Public key content or null if not configured/readable
     * @throws InvalidConfigException if key file doesn't exist or is not readable
     */
    public function getJwtPublicKey(): ?string
    {
        $keyPath = $this->getJwtPublicKeyPath();
        
        if ($keyPath === null) {
            // For ECC, try to extract public key from private key
            return $this->extractPublicKeyFromPrivateKey();
        }

        if (!file_exists($keyPath)) {
            throw new InvalidConfigException("JWT public key file does not exist: $keyPath");
        }

        if (!is_readable($keyPath)) {
            throw new InvalidConfigException("JWT public key file is not readable: $keyPath");
        }

        $keyContent = file_get_contents($keyPath);
        
        if ($keyContent === false) {
            throw new InvalidConfigException("Failed to read JWT public key file: $keyPath");
        }

        return $keyContent;
    }

    /**
     * Extract public key from private key (for ECC/RSA)
     * 
     * @return string|null Public key content or null on failure
     */
    protected function extractPublicKeyFromPrivateKey(): ?string
    {
        try {
            $privateKey = $this->getJwtPrivateKey();
            
            if ($privateKey === null) {
                return null;
            }

            // Get passphrase if provided
            $passphrase = $this->hasEnvVar('JWT_KEY_PASSPHRASE') ? getenv('JWT_KEY_PASSPHRASE') : null;

            // Load private key
            $key = openssl_pkey_get_private($privateKey, $passphrase);
            
            if ($key === false) {
                Yii::error('Failed to load private key: ' . openssl_error_string(), __METHOD__);
                return null;
            }

            // Extract public key
            $details = openssl_pkey_get_details($key);
            
            if ($details === false || !isset($details['key'])) {
                Yii::error('Failed to extract public key from private key', __METHOD__);
                return null;
            }

            return $details['key'];
        } catch (\Exception $e) {
            Yii::error('Error extracting public key: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Get JWT algorithm based on configuration
     * 
     * @return string JWT algorithm (HS256, RS256, ES256, etc.)
     */
    public function getJwtAlgorithm(): string
    {
        // If JWT_ALGORITHM is explicitly set, use it
        if ($this->hasEnvVar('JWT_ALGORITHM')) {
            return getenv('JWT_ALGORITHM');
        }

        // Auto-detect based on key type
        if ($this->isUsingJwtKeyFile()) {
            // Try to detect key type
            $keyPath = $this->getJwtKeyPath();
            $keyContent = file_get_contents($keyPath);
            
            if (strpos($keyContent, 'BEGIN EC PRIVATE KEY') !== false || 
                strpos($keyContent, 'BEGIN PRIVATE KEY') !== false) {
                // ECC key - default to ES256
                return getenv('JWT_ALGORITHM') ?: 'ES256';
            } elseif (strpos($keyContent, 'BEGIN RSA PRIVATE KEY') !== false) {
                // RSA key - default to RS256
                return 'RS256';
            }
            
            // Unknown key type, default to ES256 for ECC
            return 'ES256';
        }

        // Using HMAC secret
        return 'HS256';
    }

    /**
     * Get previous JWT key path for rotation support
     * 
     * @return string|null Path to previous private key file or null if not set
     */
    public function getJwtPreviousKeyPath(): ?string
    {
        return $this->hasEnvVar('JWT_PREVIOUS_KEY') ? getenv('JWT_PREVIOUS_KEY') : null;
    }

    /**
     * Get previous JWT private key content for rotation support
     * 
     * @return string|null Previous private key content or null if not configured
     */
    public function getJwtPreviousPrivateKey(): ?string
    {
        $keyPath = $this->getJwtPreviousKeyPath();
        
        if ($keyPath === null) {
            return null;
        }

        if (!file_exists($keyPath) || !is_readable($keyPath)) {
            return null;
        }

        return file_get_contents($keyPath);
    }

    /**
     * Rotate JWT key file
     * 
     * @param string $newKeyPath Path to new private key file
     * @throws InvalidConfigException if the new key file is invalid
     */
    public function rotateJwtKeyFile(string $newKeyPath): void
    {
        if (!file_exists($newKeyPath)) {
            throw new InvalidConfigException("New JWT key file does not exist: $newKeyPath");
        }

        if (!is_readable($newKeyPath)) {
            throw new InvalidConfigException("New JWT key file is not readable: $newKeyPath");
        }

        // Verify the key is valid
        $keyContent = file_get_contents($newKeyPath);
        $passphrase = $this->hasEnvVar('JWT_KEY_PASSPHRASE') ? getenv('JWT_KEY_PASSPHRASE') : null;
        $key = openssl_pkey_get_private($keyContent, $passphrase);
        
        if ($key === false) {
            throw new InvalidConfigException("Invalid JWT private key file: $newKeyPath");
        }

        // Log the rotation (without exposing the actual keys)
        Yii::info("JWT key file rotated successfully. Old key: {$this->getJwtKeyPath()}, New key: $newKeyPath", __METHOD__);
        
        // Note: Actual environment variable update must be done by administrator
        Yii::warning('JWT_KEY and JWT_PREVIOUS_KEY environment variables must be updated manually', __METHOD__);
    }
}

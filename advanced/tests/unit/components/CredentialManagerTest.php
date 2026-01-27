<?php

namespace tests\unit\components;

use common\components\security\CredentialManager;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\InvalidConfigException;

/**
 * CredentialManager Unit Tests
 * 
 * Tests the CredentialManager component functionality
 * 
 * Requirements: 1.1, 1.2, 1.4, 1.6
 * 
 * @group credential-manager
 * @group security
 */
class CredentialManagerTest extends TestCase
{
    /**
     * @var CredentialManager
     */
    protected $credentialManager;

    /**
     * @var array Original environment variables to restore after tests
     */
    protected $originalEnv = [];

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Save original environment variables
        $this->saveOriginalEnv();
        
        // Set up test environment variables
        $this->setTestEnv();
    }

    /**
     * Clean up test environment
     */
    protected function tearDown(): void
    {
        // Restore original environment variables
        $this->restoreOriginalEnv();
        
        parent::tearDown();
    }

    /**
     * Save original environment variables
     */
    protected function saveOriginalEnv(): void
    {
        $vars = [
            'JWT_KEY',
            'MYSQL_HOST',
            'MYSQL_PORT',
            'MYSQL_DB',
            'MYSQL_USERNAME',
            'MYSQL_PASSWORD',
            'REDIS_HOST',
            'REDIS_PORT',
            'REDIS_PASSWORD',
            'REDIS_SECURITY_DB',
            'REDIS_TIMEOUT',
        ];

        foreach ($vars as $var) {
            $value = getenv($var);
            $this->originalEnv[$var] = $value !== false ? $value : null;
        }
    }

    /**
     * Restore original environment variables
     */
    protected function restoreOriginalEnv(): void
    {
        // Clean up temporary JWT key file
        if (isset($this->originalEnv['_temp_jwt_key']) && file_exists($this->originalEnv['_temp_jwt_key'])) {
            unlink($this->originalEnv['_temp_jwt_key']);
            unset($this->originalEnv['_temp_jwt_key']);
        }
        
        foreach ($this->originalEnv as $var => $value) {
            if ($value === null) {
                putenv($var);
            } else {
                putenv("$var=$value");
            }
        }
    }

    /**
     * Set up test environment variables
     */
    protected function setTestEnv(): void
    {
        // Create a temporary JWT key file for testing
        $tempKeyPath = sys_get_temp_dir() . '/test_jwt_key_' . uniqid() . '.pem';
        $keyContent = <<<EOT
-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIIGlRHdqoq3+9VXKzqPr8KHvYc7qJ5xKzqPr8KHvYc7qoAoGCCqGSM49
AwEHoUQDQgAEzqPr8KHvYc7qJ5xKzqPr8KHvYc7qJ5xKzqPr8KHvYc7qJ5xKzqPr
8KHvYc7qJ5xKzqPr8KHvYc7qJ5xKzqPr8A==
-----END EC PRIVATE KEY-----
EOT;
        file_put_contents($tempKeyPath, $keyContent);
        
        putenv("JWT_KEY=$tempKeyPath");
        putenv('MYSQL_HOST=127.0.0.1');
        putenv('MYSQL_PORT=3306');
        putenv('MYSQL_DB=yii2_advanced_test');
        putenv('MYSQL_USERNAME=root');
        putenv('MYSQL_PASSWORD=root');
        putenv('REDIS_HOST=127.0.0.1');
        putenv('REDIS_PORT=6379');
        putenv('REDIS_PASSWORD=');
        putenv('REDIS_SECURITY_DB=1');
        putenv('REDIS_TIMEOUT=2.5');
        
        // Store temp file path for cleanup
        $this->originalEnv['_temp_jwt_key'] = $tempKeyPath;
    }

    /**
     * Test successful initialization with all required environment variables
     * Requirement 1.2: Validate that all required secrets are present
     */
    public function testSuccessfulInitialization()
    {
        $this->credentialManager = new CredentialManager();
        $this->assertInstanceOf(CredentialManager::class, $this->credentialManager);
    }

    /**
     * Test initialization fails when JWT_SECRET is missing
     * Requirement 1.2: Validate that all required secrets are present
     */
    public function testInitializationFailsWithMissingJwtSecret()
    {
        putenv('JWT_SECRET');
        
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Required environment variables are missing');
        
        new CredentialManager();
    }

    /**
     * Test initialization fails when JWT_SECRET is too short
     * Requirement 1.2: Validate that all required secrets are present
     */
    public function testInitializationFailsWithShortJwtSecret()
    {
        putenv('JWT_SECRET=short');
        
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Required environment variables are missing');
        
        new CredentialManager();
    }

    /**
     * Test initialization fails when DB_HOST is missing
     * Requirement 1.2: Validate that all required secrets are present
     */
    public function testInitializationFailsWithMissingDbHost()
    {
        putenv('DB_HOST');
        
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Required environment variables are missing');
        
        new CredentialManager();
    }

    /**
     * Test validateEnvironment returns true with all required variables
     * Requirement 1.2: Validate that all required secrets are present
     */
    public function testValidateEnvironmentSuccess()
    {
        $this->credentialManager = new CredentialManager();
        $this->assertTrue($this->credentialManager->validateEnvironment());
    }

    /**
     * Test validateEnvironment returns false with missing variables
     * Requirement 1.2: Validate that all required secrets are present
     */
    public function testValidateEnvironmentFailure()
    {
        $this->credentialManager = new CredentialManager();
        
        // Remove a required variable
        putenv('DB_NAME');
        
        $this->assertFalse($this->credentialManager->validateEnvironment());
    }

    /**
     * Test getDatabaseCredentials returns correct values
     * Requirement 1.4: Use environment variables for database credentials
     */
    public function testGetDatabaseCredentials()
    {
        $this->credentialManager = new CredentialManager();
        $credentials = $this->credentialManager->getDatabaseCredentials();
        
        $this->assertIsArray($credentials);
        $this->assertEquals('localhost', $credentials['host']);
        $this->assertEquals('3306', $credentials['port']);
        $this->assertEquals('test_db', $credentials['database']);
        $this->assertEquals('test_user', $credentials['username']);
        $this->assertEquals('test_password', $credentials['password']);
    }

    /**
     * Test getDatabaseCredentials uses default port when not set
     * Requirement 1.4: Use environment variables for database credentials
     */
    public function testGetDatabaseCredentialsDefaultPort()
    {
        putenv('DB_PORT');
        
        $this->credentialManager = new CredentialManager();
        $credentials = $this->credentialManager->getDatabaseCredentials();
        
        $this->assertEquals('3306', $credentials['port']);
    }

    /**
     * Test getJwtSecret returns correct value
     * Requirement 1.4: Use environment variables for JWT secrets
     */
    public function testGetJwtSecret()
    {
        $this->credentialManager = new CredentialManager();
        $secret = $this->credentialManager->getJwtSecret();
        
        $this->assertEquals('test-secret-key-at-least-32-characters-long-for-testing', $secret);
    }

    /**
     * Test getJwtSecret caches the value
     * Requirement 1.4: Use environment variables for JWT secrets
     */
    public function testGetJwtSecretCaching()
    {
        $this->credentialManager = new CredentialManager();
        
        $secret1 = $this->credentialManager->getJwtSecret();
        
        // Change environment variable
        putenv('JWT_SECRET=new-secret-key-at-least-32-characters-long-for-testing');
        
        // Should still return cached value
        $secret2 = $this->credentialManager->getJwtSecret();
        
        $this->assertEquals($secret1, $secret2);
    }

    /**
     * Test getJwtPreviousSecret returns correct value
     * Requirement 1.6: Support JWT key rotation
     */
    public function testGetJwtPreviousSecret()
    {
        $this->credentialManager = new CredentialManager();
        $previousSecret = $this->credentialManager->getJwtPreviousSecret();
        
        $this->assertEquals('old-secret-key-at-least-32-characters-long-for-testing', $previousSecret);
    }

    /**
     * Test getJwtPreviousSecret returns null when not set
     * Requirement 1.6: Support JWT key rotation
     */
    public function testGetJwtPreviousSecretNotSet()
    {
        putenv('JWT_PREVIOUS_SECRET');
        
        $this->credentialManager = new CredentialManager();
        $previousSecret = $this->credentialManager->getJwtPreviousSecret();
        
        $this->assertNull($previousSecret);
    }

    /**
     * Test getJwtKeyRotationGracePeriod returns correct value
     * Requirement 1.6: JWT key rotation with grace period
     */
    public function testGetJwtKeyRotationGracePeriod()
    {
        $this->credentialManager = new CredentialManager();
        $gracePeriod = $this->credentialManager->getJwtKeyRotationGracePeriod();
        
        $this->assertEquals(86400, $gracePeriod);
    }

    /**
     * Test getJwtKeyRotationGracePeriod returns default when not set
     * Requirement 1.6: JWT key rotation with grace period
     */
    public function testGetJwtKeyRotationGracePeriodDefault()
    {
        putenv('JWT_KEY_ROTATION_GRACE_PERIOD');
        
        $this->credentialManager = new CredentialManager();
        $gracePeriod = $this->credentialManager->getJwtKeyRotationGracePeriod();
        
        $this->assertEquals(86400, $gracePeriod); // Default 24 hours
    }

    /**
     * Test rotateJwtSecret updates secrets correctly
     * Requirement 1.6: Rotate JWT secrets at configurable intervals
     */
    public function testRotateJwtSecret()
    {
        $this->credentialManager = new CredentialManager();
        
        $oldSecret = $this->credentialManager->getJwtSecret();
        $newSecret = 'new-rotated-secret-key-at-least-32-characters-long';
        
        $this->credentialManager->rotateJwtSecret($newSecret);
        
        // Current secret should be the new one
        $this->assertEquals($newSecret, $this->credentialManager->getJwtSecret());
        
        // Previous secret should be the old one
        $this->assertEquals($oldSecret, $this->credentialManager->getJwtPreviousSecret());
    }

    /**
     * Test rotateJwtSecret fails with short secret
     * Requirement 1.6: Rotate JWT secrets at configurable intervals
     */
    public function testRotateJwtSecretFailsWithShortSecret()
    {
        $this->credentialManager = new CredentialManager();
        
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('must be at least 32 bytes long');
        
        $this->credentialManager->rotateJwtSecret('short');
    }

    /**
     * Test getRedisCredentials returns correct values
     * Requirement 1.4: Use environment variables for all credentials
     */
    public function testGetRedisCredentials()
    {
        $this->credentialManager = new CredentialManager();
        $credentials = $this->credentialManager->getRedisCredentials();
        
        $this->assertIsArray($credentials);
        $this->assertEquals('127.0.0.1', $credentials['host']);
        $this->assertEquals('6379', $credentials['port']);
        $this->assertNull($credentials['password']);
        $this->assertEquals(1, $credentials['database']);
        $this->assertEquals(2.5, $credentials['timeout']);
    }

    /**
     * Test getRedisCredentials uses fallback values
     * Requirement 1.4: Use environment variables for all credentials
     */
    public function testGetRedisCredentialsFallback()
    {
        putenv('REDIS_HOST');
        putenv('REDIS_PORT');
        
        $this->credentialManager = new CredentialManager();
        $credentials = $this->credentialManager->getRedisCredentials();
        
        $this->assertEquals('127.0.0.1', $credentials['host']);
        $this->assertEquals('6379', $credentials['port']);
    }

    /**
     * Test getApiKey returns correct value
     * Requirement 1.4: Use environment variables for API keys
     */
    public function testGetApiKey()
    {
        putenv('TEST_API_KEY=test-api-key-value');
        
        $this->credentialManager = new CredentialManager();
        $apiKey = $this->credentialManager->getApiKey('TEST_API_KEY');
        
        $this->assertEquals('test-api-key-value', $apiKey);
        
        putenv('TEST_API_KEY');
    }

    /**
     * Test getApiKey returns null when not set
     * Requirement 1.4: Use environment variables for API keys
     */
    public function testGetApiKeyNotSet()
    {
        $this->credentialManager = new CredentialManager();
        $apiKey = $this->credentialManager->getApiKey('NONEXISTENT_API_KEY');
        
        $this->assertNull($apiKey);
    }

    /**
     * Test hasEnvVar returns true for existing variable
     */
    public function testHasEnvVarExists()
    {
        $this->credentialManager = new CredentialManager();
        $this->assertTrue($this->credentialManager->hasEnvVar('JWT_SECRET'));
    }

    /**
     * Test hasEnvVar returns false for non-existing variable
     */
    public function testHasEnvVarNotExists()
    {
        $this->credentialManager = new CredentialManager();
        $this->assertFalse($this->credentialManager->hasEnvVar('NONEXISTENT_VAR'));
    }

    /**
     * Test getMissingEnvVars returns empty array when all present
     * Requirement 1.2: Validate that all required secrets are present
     */
    public function testGetMissingEnvVarsEmpty()
    {
        $this->credentialManager = new CredentialManager();
        $missing = $this->credentialManager->getMissingEnvVars();
        
        $this->assertIsArray($missing);
        $this->assertEmpty($missing);
    }

    /**
     * Test getMissingEnvVars returns missing variables
     * Requirement 1.2: Validate that all required secrets are present
     */
    public function testGetMissingEnvVarsWithMissing()
    {
        // Create the manager first with valid environment
        $this->credentialManager = new CredentialManager();
        
        // Now remove some variables
        putenv('DB_NAME');
        putenv('DB_USERNAME');
        
        // Check for missing variables
        $missing = $this->credentialManager->getMissingEnvVars();
        
        $this->assertIsArray($missing);
        $this->assertContains('DB_NAME', $missing);
        $this->assertContains('DB_USERNAME', $missing);
    }

    /**
     * Test boundary condition: JWT secret exactly 32 bytes
     * Requirement 1.2: JWT secret must be at least 32 bytes
     */
    public function testJwtSecretExactly32Bytes()
    {
        putenv('JWT_SECRET=12345678901234567890123456789012'); // Exactly 32 bytes
        
        $this->credentialManager = new CredentialManager();
        $this->assertTrue($this->credentialManager->validateEnvironment());
    }

    /**
     * Test boundary condition: JWT secret 31 bytes (should fail)
     * Requirement 1.2: JWT secret must be at least 32 bytes
     */
    public function testJwtSecret31Bytes()
    {
        putenv('JWT_SECRET=1234567890123456789012345678901'); // 31 bytes
        
        $this->expectException(InvalidConfigException::class);
        new CredentialManager();
    }

    /**
     * Test getEncryptedConfig encrypts values
     * Requirement 1.1: Secure storage of configuration values
     */
    public function testGetEncryptedConfig()
    {
        putenv('TEST_CONFIG_VALUE=sensitive-data');
        
        $this->credentialManager = new CredentialManager();
        $encrypted = $this->credentialManager->getEncryptedConfig('TEST_CONFIG_VALUE');
        
        $this->assertNotNull($encrypted);
        $this->assertNotEquals('sensitive-data', $encrypted);
        
        putenv('TEST_CONFIG_VALUE');
    }

    /**
     * Test getEncryptedConfig returns null for non-existent key
     * Requirement 1.1: Secure storage of configuration values
     */
    public function testGetEncryptedConfigNonExistent()
    {
        $this->credentialManager = new CredentialManager();
        $encrypted = $this->credentialManager->getEncryptedConfig('NONEXISTENT_KEY');
        
        $this->assertNull($encrypted);
    }

    /**
     * Test decryptConfig decrypts values correctly
     * Requirement 1.1: Secure storage of configuration values
     */
    public function testDecryptConfig()
    {
        putenv('TEST_CONFIG_VALUE=sensitive-data');
        
        $this->credentialManager = new CredentialManager();
        $encrypted = $this->credentialManager->getEncryptedConfig('TEST_CONFIG_VALUE');
        $decrypted = $this->credentialManager->decryptConfig($encrypted);
        
        $this->assertEquals('sensitive-data', $decrypted);
        
        putenv('TEST_CONFIG_VALUE');
    }
}

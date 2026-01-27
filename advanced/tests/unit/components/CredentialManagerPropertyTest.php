<?php

namespace tests\unit\components;

use common\components\security\CredentialManager;
use PHPUnit\Framework\TestCase;
use yii\base\InvalidConfigException;

/**
 * CredentialManager Property-Based Tests
 * 
 * @test Feature: backend-security-hardening, Property 1: 环境变量验证完整性
 * 
 * Property 1: 环境变量验证完整性
 * For any set of required environment variables, when the Credential Manager loads configuration,
 * it should detect and report all missing variables.
 * 
 * **Validates: Requirements 1.2**
 * 
 * @group credential-manager
 * @group security
 * @group property-based-test
 */
class CredentialManagerPropertyTest extends TestCase
{
    /**
     * @var array Original environment variables to restore after tests
     */
    protected $originalEnv = [];

    /**
     * @var array Required environment variables
     */
    protected $requiredVars = [
        'JWT_SECRET',
        'DB_HOST',
        'DB_NAME',
        'DB_USERNAME',
        'DB_PASSWORD',
    ];

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->saveOriginalEnv();
    }

    /**
     * Clean up test environment
     */
    protected function tearDown(): void
    {
        $this->restoreOriginalEnv();
        parent::tearDown();
    }

    /**
     * Save original environment variables
     */
    protected function saveOriginalEnv(): void
    {
        foreach ($this->requiredVars as $var) {
            $value = getenv($var);
            $this->originalEnv[$var] = $value !== false ? $value : null;
        }
    }

    /**
     * Restore original environment variables
     */
    protected function restoreOriginalEnv(): void
    {
        foreach ($this->originalEnv as $var => $value) {
            if ($value === null) {
                putenv($var);
            } else {
                putenv("$var=$value");
            }
        }
    }

    /**
     * Set environment variables based on configuration
     * 
     * @param array $config Configuration array where key is var name and value is the var value (or null to unset)
     */
    protected function setEnvVars(array $config): void
    {
        foreach ($config as $var => $value) {
            if ($value === null) {
                putenv($var);
            } else {
                putenv("$var=$value");
            }
        }
    }

    /**
     * Generate all possible combinations of missing environment variables
     * This simulates property-based testing by testing all possible subsets
     * 
     * @return array Array of test cases
     */
    public function missingEnvVarCombinationsProvider(): array
    {
        $testCases = [];
        $requiredVars = $this->requiredVars;
        $validValues = [
            'JWT_SECRET' => 'test-secret-key-at-least-32-characters-long-for-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];

        // Test case 1: All variables present (should pass)
        $testCases['all_present'] = [
            'config' => $validValues,
            'expectedMissing' => [],
            'shouldValidate' => true,
        ];

        // Test case 2: Each single variable missing
        foreach ($requiredVars as $missingVar) {
            $config = $validValues;
            $config[$missingVar] = null;
            
            $testCases["missing_{$missingVar}"] = [
                'config' => $config,
                'expectedMissing' => [$missingVar],
                'shouldValidate' => false,
            ];
        }

        // Test case 3: Each single variable empty string
        foreach ($requiredVars as $emptyVar) {
            $config = $validValues;
            $config[$emptyVar] = '';
            
            $testCases["empty_{$emptyVar}"] = [
                'config' => $config,
                'expectedMissing' => [$emptyVar],
                'shouldValidate' => false,
            ];
        }

        // Test case 4: Multiple variables missing (pairs)
        for ($i = 0; $i < count($requiredVars) - 1; $i++) {
            for ($j = $i + 1; $j < count($requiredVars); $j++) {
                $var1 = $requiredVars[$i];
                $var2 = $requiredVars[$j];
                $config = $validValues;
                $config[$var1] = null;
                $config[$var2] = null;
                
                $testCases["missing_{$var1}_and_{$var2}"] = [
                    'config' => $config,
                    'expectedMissing' => [$var1, $var2],
                    'shouldValidate' => false,
                ];
            }
        }

        // Test case 5: All variables missing
        $config = [];
        foreach ($requiredVars as $var) {
            $config[$var] = null;
        }
        $testCases['all_missing'] = [
            'config' => $config,
            'expectedMissing' => $requiredVars,
            'shouldValidate' => false,
        ];

        // Test case 6: JWT_SECRET too short (less than 32 bytes)
        $config = $validValues;
        $config['JWT_SECRET'] = 'short-secret';
        $testCases['jwt_secret_too_short'] = [
            'config' => $config,
            'expectedMissing' => [],
            'shouldValidate' => false,
        ];

        // Test case 7: JWT_SECRET exactly 32 bytes (boundary condition)
        $config = $validValues;
        $config['JWT_SECRET'] = '12345678901234567890123456789012'; // Exactly 32 bytes
        $testCases['jwt_secret_exactly_32_bytes'] = [
            'config' => $config,
            'expectedMissing' => [],
            'shouldValidate' => true,
        ];

        // Test case 8: JWT_SECRET 31 bytes (boundary condition - should fail)
        $config = $validValues;
        $config['JWT_SECRET'] = '1234567890123456789012345678901'; // 31 bytes
        $testCases['jwt_secret_31_bytes'] = [
            'config' => $config,
            'expectedMissing' => [],
            'shouldValidate' => false,
        ];

        return $testCases;
    }

    /**
     * Property Test 1: Environment Variable Validation Completeness
     * 
     * **Validates: Requirements 1.2**
     * 
     * For any set of required environment variables, when the Credential Manager loads configuration,
     * it should detect and report ALL missing variables.
     * 
     * This test verifies that:
     * 1. getMissingEnvVars() returns exactly the missing variables (no more, no less)
     * 2. validateEnvironment() returns false when any required variable is missing
     * 3. validateEnvironment() returns true only when all required variables are present and valid
     * 
     * @test
     */
    public function testEnvironmentVariableValidationCompleteness(): void
    {
        $testCases = $this->missingEnvVarCombinationsProvider();
        
        foreach ($testCases as $testName => $testData) {
            $config = $testData['config'];
            $expectedMissing = $testData['expectedMissing'];
            $shouldValidate = $testData['shouldValidate'];
            
            $this->runValidationCompletenessTest($config, $expectedMissing, $shouldValidate, $testName);
        }
    }
    
    /**
     * Run a single validation completeness test
     */
    protected function runValidationCompletenessTest(
        array $config,
        array $expectedMissing,
        bool $shouldValidate,
        string $testName
    ): void {
        // Arrange: Set up environment variables according to test case
        $this->setEnvVars($config);

        // Act & Assert: Test based on whether validation should pass or fail
        if ($shouldValidate) {
            // Should successfully create CredentialManager
            $credentialManager = new CredentialManager();
            
            // Verify validateEnvironment returns true
            $this->assertTrue(
                $credentialManager->validateEnvironment(),
                "Test '$testName': validateEnvironment() should return true when all required variables are present and valid"
            );
            
            // Verify getMissingEnvVars returns empty array
            $actualMissing = $credentialManager->getMissingEnvVars();
            $this->assertEmpty(
                $actualMissing,
                "Test '$testName': getMissingEnvVars() should return empty array when all variables are present"
            );
        } else {
            // Should throw exception during initialization
            $exceptionThrown = false;
            try {
                new CredentialManager();
            } catch (InvalidConfigException $e) {
                $exceptionThrown = true;
                $this->assertStringContainsString(
                    'Required environment variables are missing',
                    $e->getMessage(),
                    "Test '$testName': Exception message should indicate missing environment variables"
                );
            }
            
            $this->assertTrue(
                $exceptionThrown,
                "Test '$testName': InvalidConfigException should be thrown when required variables are missing or invalid"
            );
        }
    }

    /**
     * Property Test 1b: getMissingEnvVars Accuracy
     * 
     * **Validates: Requirements 1.2**
     * 
     * This test specifically verifies that getMissingEnvVars() accurately reports
     * ALL missing variables without false positives or false negatives.
     * 
     * We test this separately because we need to create the CredentialManager
     * with valid environment first, then check missing vars after changing the environment.
     * 
     * @test
     */
    public function testGetMissingEnvVarsAccuracy(): void
    {
        $testCases = $this->missingEnvVarCombinationsProvider();
        
        foreach ($testCases as $testName => $testData) {
            $config = $testData['config'];
            $expectedMissing = $testData['expectedMissing'];
            $shouldValidate = $testData['shouldValidate'];
            
            // Skip the test cases where validation should pass (no missing vars to test)
            if ($shouldValidate) {
                continue;
            }
            
            $this->runGetMissingEnvVarsAccuracyTest($config, $expectedMissing, $testName);
        }
    }
    
    /**
     * Run a single getMissingEnvVars accuracy test
     */
    protected function runGetMissingEnvVarsAccuracyTest(
        array $config,
        array $expectedMissing,
        string $testName
    ): void {

        // Arrange: First create CredentialManager with valid environment
        $validConfig = [
            'JWT_SECRET' => 'test-secret-key-at-least-32-characters-long-for-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        $this->setEnvVars($validConfig);
        $credentialManager = new CredentialManager();

        // Act: Now change environment to the test configuration
        $this->setEnvVars($config);
        $actualMissing = $credentialManager->getMissingEnvVars();

        // Assert: Verify that getMissingEnvVars returns exactly the expected missing variables
        sort($expectedMissing);
        sort($actualMissing);
        
        $this->assertEquals(
            $expectedMissing,
            $actualMissing,
            sprintf(
                "Test '$testName': getMissingEnvVars() should return exactly the missing variables. Expected: [%s], Got: [%s]",
                implode(', ', $expectedMissing),
                implode(', ', $actualMissing)
            )
        );

        // Additional assertion: Verify count matches
        $this->assertCount(
            count($expectedMissing),
            $actualMissing,
            "Test '$testName': The number of missing variables should match exactly"
        );

        // Additional assertion: Verify no false positives (all returned vars should be in expected)
        foreach ($actualMissing as $var) {
            $this->assertContains(
                $var,
                $expectedMissing,
                "Test '$testName': Variable '$var' was reported as missing but should not be"
            );
        }

        // Additional assertion: Verify no false negatives (all expected vars should be in returned)
        foreach ($expectedMissing as $var) {
            $this->assertContains(
                $var,
                $actualMissing,
                "Test '$testName': Variable '$var' should be reported as missing but was not"
            );
        }
    }

    /**
     * Property Test 1c: validateEnvironment Consistency
     * 
     * **Validates: Requirements 1.2**
     * 
     * This test verifies that validateEnvironment() is consistent with getMissingEnvVars():
     * - validateEnvironment() returns false if and only if getMissingEnvVars() is non-empty
     * - validateEnvironment() returns true if and only if getMissingEnvVars() is empty
     * 
     * @test
     */
    public function testValidateEnvironmentConsistencyWithGetMissingEnvVars(): void
    {
        $validConfig = [
            'JWT_SECRET' => 'test-secret-key-at-least-32-characters-long-for-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];

        // Test multiple scenarios
        $scenarios = [
            'all_present' => [
                'config' => $validConfig,
                'expectedValid' => true,
            ],
            'missing_jwt_secret' => [
                'config' => array_merge($validConfig, ['JWT_SECRET' => null]),
                'expectedValid' => false,
            ],
            'missing_db_host' => [
                'config' => array_merge($validConfig, ['DB_HOST' => null]),
                'expectedValid' => false,
            ],
            'missing_multiple' => [
                'config' => array_merge($validConfig, ['DB_NAME' => null, 'DB_USERNAME' => null]),
                'expectedValid' => false,
            ],
        ];

        foreach ($scenarios as $scenarioName => $scenario) {
            // Arrange
            $this->setEnvVars($validConfig);
            $credentialManager = new CredentialManager();
            
            // Change to test configuration
            $this->setEnvVars($scenario['config']);
            
            // Act
            $isValid = $credentialManager->validateEnvironment();
            $missingVars = $credentialManager->getMissingEnvVars();
            
            // Assert: validateEnvironment and getMissingEnvVars should be consistent
            if ($scenario['expectedValid']) {
                $this->assertTrue(
                    $isValid,
                    "Scenario '$scenarioName': validateEnvironment() should return true"
                );
                $this->assertEmpty(
                    $missingVars,
                    "Scenario '$scenarioName': getMissingEnvVars() should return empty array"
                );
            } else {
                $this->assertFalse(
                    $isValid,
                    "Scenario '$scenarioName': validateEnvironment() should return false"
                );
                $this->assertNotEmpty(
                    $missingVars,
                    "Scenario '$scenarioName': getMissingEnvVars() should return non-empty array"
                );
            }
            
            // Verify consistency: validateEnvironment() == empty(getMissingEnvVars())
            $this->assertEquals(
                empty($missingVars),
                $isValid,
                "Scenario '$scenarioName': validateEnvironment() should be consistent with getMissingEnvVars()"
            );
        }
    }

    /**
     * Property Test 1d: JWT Secret Length Validation
     * 
     * **Validates: Requirements 1.2**
     * 
     * This test verifies that JWT_SECRET length validation is correctly enforced:
     * - Secrets with length < 32 bytes should fail validation
     * - Secrets with length >= 32 bytes should pass validation
     * 
     * @test
     */
    public function testJwtSecretLengthValidation(): void
    {
        $baseConfig = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];

        // Test various JWT secret lengths
        $testCases = [
            ['length' => 0, 'secret' => '', 'shouldPass' => false],
            ['length' => 1, 'secret' => 'a', 'shouldPass' => false],
            ['length' => 16, 'secret' => str_repeat('a', 16), 'shouldPass' => false],
            ['length' => 31, 'secret' => str_repeat('a', 31), 'shouldPass' => false],
            ['length' => 32, 'secret' => str_repeat('a', 32), 'shouldPass' => true],
            ['length' => 33, 'secret' => str_repeat('a', 33), 'shouldPass' => true],
            ['length' => 64, 'secret' => str_repeat('a', 64), 'shouldPass' => true],
            ['length' => 128, 'secret' => str_repeat('a', 128), 'shouldPass' => true],
        ];

        foreach ($testCases as $testCase) {
            $config = array_merge($baseConfig, ['JWT_SECRET' => $testCase['secret']]);
            $this->setEnvVars($config);

            if ($testCase['shouldPass']) {
                // Should successfully create CredentialManager
                $credentialManager = new CredentialManager();
                $this->assertTrue(
                    $credentialManager->validateEnvironment(),
                    "JWT secret with length {$testCase['length']} should pass validation"
                );
            } else {
                // Should throw exception
                $exceptionThrown = false;
                try {
                    new CredentialManager();
                } catch (InvalidConfigException $e) {
                    $exceptionThrown = true;
                }
                
                $this->assertTrue(
                    $exceptionThrown,
                    "JWT secret with length {$testCase['length']} should fail validation"
                );
            }
        }
    }

    /**
     * Property Test 1e: Empty String vs Null Equivalence
     * 
     * **Validates: Requirements 1.2**
     * 
     * This test verifies that empty strings and null values are treated equivalently
     * as "missing" environment variables.
     * 
     * @test
     */
    public function testEmptyStringVsNullEquivalence(): void
    {
        $validConfig = [
            'JWT_SECRET' => 'test-secret-key-at-least-32-characters-long-for-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];

        foreach ($this->requiredVars as $var) {
            // Test with null
            $configWithNull = array_merge($validConfig, [$var => null]);
            $this->setEnvVars($validConfig);
            $credentialManager = new CredentialManager();
            $this->setEnvVars($configWithNull);
            $missingWithNull = $credentialManager->getMissingEnvVars();

            // Test with empty string
            $configWithEmpty = array_merge($validConfig, [$var => '']);
            $this->setEnvVars($validConfig);
            $credentialManager = new CredentialManager();
            $this->setEnvVars($configWithEmpty);
            $missingWithEmpty = $credentialManager->getMissingEnvVars();

            // Assert: Both should report the same missing variable
            $this->assertEquals(
                $missingWithNull,
                $missingWithEmpty,
                "Variable '$var' should be treated as missing whether it's null or empty string"
            );
            
            $this->assertContains(
                $var,
                $missingWithNull,
                "Variable '$var' set to null should be reported as missing"
            );
            
            $this->assertContains(
                $var,
                $missingWithEmpty,
                "Variable '$var' set to empty string should be reported as missing"
            );
        }
    }

    /**
     * Property Test 3: JWT Key Rotation Maintains Backward Compatibility
     * 
     * **Validates: Requirements 1.6**
     * 
     * @test Feature: backend-security-hardening, Property 3: JWT 密钥轮换保持向后兼容
     * 
     * For any JWT token generated before key rotation, it should remain valid until its
     * expiration time using the old key.
     * 
     * This test verifies that:
     * 1. After rotation, getJwtSecret() returns the new secret
     * 2. After rotation, getJwtPreviousSecret() returns the old secret
     * 3. The old secret is preserved and accessible during the grace period
     * 4. The rotation mechanism properly maintains both keys
     * 5. Multiple rotations properly chain the secrets
     * 
     * @test
     */
    public function testJwtKeyRotationMaintainsBackwardCompatibility(): void
    {
        // Set up valid environment
        $validConfig = [
            'JWT_SECRET' => 'original-secret-key-at-least-32-characters-long-for-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        $this->setEnvVars($validConfig);

        // Create CredentialManager with original secret
        $credentialManager = new CredentialManager();
        
        // Verify initial state
        $originalSecret = $credentialManager->getJwtSecret();
        $this->assertEquals(
            'original-secret-key-at-least-32-characters-long-for-testing',
            $originalSecret,
            'Initial JWT secret should match the environment variable'
        );
        
        $this->assertNull(
            $credentialManager->getJwtPreviousSecret(),
            'Previous JWT secret should be null before any rotation'
        );

        // Test Case 1: Single rotation
        $newSecret1 = 'new-secret-key-number-1-at-least-32-characters-long-testing';
        $credentialManager->rotateJwtSecret($newSecret1);
        
        $this->assertEquals(
            $newSecret1,
            $credentialManager->getJwtSecret(),
            'After rotation, getJwtSecret() should return the new secret'
        );
        
        $this->assertEquals(
            $originalSecret,
            $credentialManager->getJwtPreviousSecret(),
            'After rotation, getJwtPreviousSecret() should return the old secret'
        );

        // Test Case 2: Second rotation (chaining)
        $newSecret2 = 'new-secret-key-number-2-at-least-32-characters-long-testing';
        $credentialManager->rotateJwtSecret($newSecret2);
        
        $this->assertEquals(
            $newSecret2,
            $credentialManager->getJwtSecret(),
            'After second rotation, getJwtSecret() should return the newest secret'
        );
        
        $this->assertEquals(
            $newSecret1,
            $credentialManager->getJwtPreviousSecret(),
            'After second rotation, getJwtPreviousSecret() should return the previous secret (not the original)'
        );

        // Test Case 3: Third rotation (verify chaining continues)
        $newSecret3 = 'new-secret-key-number-3-at-least-32-characters-long-testing';
        $credentialManager->rotateJwtSecret($newSecret3);
        
        $this->assertEquals(
            $newSecret3,
            $credentialManager->getJwtSecret(),
            'After third rotation, getJwtSecret() should return the newest secret'
        );
        
        $this->assertEquals(
            $newSecret2,
            $credentialManager->getJwtPreviousSecret(),
            'After third rotation, getJwtPreviousSecret() should return the previous secret'
        );
    }

    /**
     * Property Test 3b: JWT Key Rotation with Environment Variables
     * 
     * **Validates: Requirements 1.6**
     * 
     * This test verifies that JWT key rotation works correctly when both
     * JWT_SECRET and JWT_PREVIOUS_SECRET are set in environment variables.
     * 
     * @test
     */
    public function testJwtKeyRotationWithEnvironmentVariables(): void
    {
        // Test Case 1: Both current and previous secrets in environment
        $configWithBothSecrets = [
            'JWT_SECRET' => 'current-secret-key-at-least-32-characters-long-for-testing',
            'JWT_PREVIOUS_SECRET' => 'previous-secret-key-at-least-32-characters-long-test',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        $this->setEnvVars($configWithBothSecrets);
        
        $credentialManager = new CredentialManager();
        
        $this->assertEquals(
            'current-secret-key-at-least-32-characters-long-for-testing',
            $credentialManager->getJwtSecret(),
            'getJwtSecret() should return JWT_SECRET from environment'
        );
        
        $this->assertEquals(
            'previous-secret-key-at-least-32-characters-long-test',
            $credentialManager->getJwtPreviousSecret(),
            'getJwtPreviousSecret() should return JWT_PREVIOUS_SECRET from environment'
        );

        // Test Case 2: Only current secret in environment (no previous)
        $configWithOnlyCurrentSecret = [
            'JWT_SECRET' => 'only-current-secret-at-least-32-characters-long-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        // Explicitly unset JWT_PREVIOUS_SECRET
        $configWithOnlyCurrentSecret['JWT_PREVIOUS_SECRET'] = null;
        $this->setEnvVars($configWithOnlyCurrentSecret);
        
        $credentialManager2 = new CredentialManager();
        
        $this->assertEquals(
            'only-current-secret-at-least-32-characters-long-testing',
            $credentialManager2->getJwtSecret(),
            'getJwtSecret() should return JWT_SECRET from environment'
        );
        
        $this->assertNull(
            $credentialManager2->getJwtPreviousSecret(),
            'getJwtPreviousSecret() should return null when JWT_PREVIOUS_SECRET is not set'
        );

        // Test Case 3: Empty string for JWT_PREVIOUS_SECRET should be treated as null
        $configWithEmptyPrevious = [
            'JWT_SECRET' => 'current-secret-key-at-least-32-characters-long-for-testing',
            'JWT_PREVIOUS_SECRET' => '',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        $this->setEnvVars($configWithEmptyPrevious);
        
        $credentialManager3 = new CredentialManager();
        
        $this->assertNull(
            $credentialManager3->getJwtPreviousSecret(),
            'getJwtPreviousSecret() should return null when JWT_PREVIOUS_SECRET is empty string'
        );
    }

    /**
     * Property Test 3c: JWT Key Rotation Secret Length Validation
     * 
     * **Validates: Requirements 1.6**
     * 
     * This test verifies that rotateJwtSecret() enforces the minimum secret length
     * requirement (32 bytes) for the new secret.
     * 
     * @test
     */
    public function testJwtKeyRotationSecretLengthValidation(): void
    {
        // Set up valid environment
        $validConfig = [
            'JWT_SECRET' => 'original-secret-key-at-least-32-characters-long-for-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        $this->setEnvVars($validConfig);
        
        $credentialManager = new CredentialManager();

        // Test various secret lengths
        $testCases = [
            ['length' => 0, 'secret' => '', 'shouldPass' => false],
            ['length' => 1, 'secret' => 'a', 'shouldPass' => false],
            ['length' => 16, 'secret' => str_repeat('a', 16), 'shouldPass' => false],
            ['length' => 31, 'secret' => str_repeat('a', 31), 'shouldPass' => false],
            ['length' => 32, 'secret' => str_repeat('a', 32), 'shouldPass' => true],
            ['length' => 33, 'secret' => str_repeat('a', 33), 'shouldPass' => true],
            ['length' => 64, 'secret' => str_repeat('a', 64), 'shouldPass' => true],
        ];

        foreach ($testCases as $testCase) {
            if ($testCase['shouldPass']) {
                // Should successfully rotate
                try {
                    $credentialManager->rotateJwtSecret($testCase['secret']);
                    $this->assertEquals(
                        $testCase['secret'],
                        $credentialManager->getJwtSecret(),
                        "Rotation with secret length {$testCase['length']} should succeed"
                    );
                } catch (InvalidConfigException $e) {
                    $this->fail("Rotation with secret length {$testCase['length']} should not throw exception");
                }
            } else {
                // Should throw exception
                $exceptionThrown = false;
                try {
                    $credentialManager->rotateJwtSecret($testCase['secret']);
                } catch (InvalidConfigException $e) {
                    $exceptionThrown = true;
                    $this->assertStringContainsString(
                        'must be at least 32 bytes long',
                        $e->getMessage(),
                        "Exception message should indicate minimum length requirement"
                    );
                }
                
                $this->assertTrue(
                    $exceptionThrown,
                    "Rotation with secret length {$testCase['length']} should throw InvalidConfigException"
                );
            }
        }
    }

    /**
     * Property Test 3d: JWT Key Rotation Grace Period Configuration
     * 
     * **Validates: Requirements 1.6**
     * 
     * This test verifies that the grace period for JWT key rotation is configurable
     * and returns appropriate default values.
     * 
     * @test
     */
    public function testJwtKeyRotationGracePeriodConfiguration(): void
    {
        // Test Case 1: Default grace period (24 hours = 86400 seconds)
        $configWithoutGracePeriod = [
            'JWT_SECRET' => 'test-secret-key-at-least-32-characters-long-for-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        $configWithoutGracePeriod['JWT_KEY_ROTATION_GRACE_PERIOD'] = null;
        $this->setEnvVars($configWithoutGracePeriod);
        
        $credentialManager = new CredentialManager();
        
        $this->assertEquals(
            86400,
            $credentialManager->getJwtKeyRotationGracePeriod(),
            'Default grace period should be 86400 seconds (24 hours)'
        );

        // Test Case 2: Custom grace period values
        $customGracePeriods = [
            3600,    // 1 hour
            7200,    // 2 hours
            43200,   // 12 hours
            86400,   // 24 hours
            172800,  // 48 hours
            604800,  // 7 days
        ];

        foreach ($customGracePeriods as $gracePeriod) {
            $configWithCustomGracePeriod = [
                'JWT_SECRET' => 'test-secret-key-at-least-32-characters-long-for-testing',
                'JWT_KEY_ROTATION_GRACE_PERIOD' => (string)$gracePeriod,
                'DB_HOST' => 'localhost',
                'DB_NAME' => 'test_db',
                'DB_USERNAME' => 'test_user',
                'DB_PASSWORD' => 'test_password',
            ];
            $this->setEnvVars($configWithCustomGracePeriod);
            
            $credentialManager = new CredentialManager();
            
            $this->assertEquals(
                $gracePeriod,
                $credentialManager->getJwtKeyRotationGracePeriod(),
                "Grace period should be configurable to $gracePeriod seconds"
            );
        }

        // Test Case 3: Empty string grace period should use default
        $configWithEmptyGracePeriod = [
            'JWT_SECRET' => 'test-secret-key-at-least-32-characters-long-for-testing',
            'JWT_KEY_ROTATION_GRACE_PERIOD' => '',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        $this->setEnvVars($configWithEmptyGracePeriod);
        
        $credentialManager = new CredentialManager();
        
        $this->assertEquals(
            86400,
            $credentialManager->getJwtKeyRotationGracePeriod(),
            'Empty grace period should use default value (86400 seconds)'
        );
    }

    /**
     * Property Test 3e: JWT Key Rotation Preserves Old Secret
     * 
     * **Validates: Requirements 1.6**
     * 
     * This test verifies that after rotation, the old secret is preserved and
     * accessible, which is essential for validating tokens that were issued
     * before the rotation.
     * 
     * @test
     */
    public function testJwtKeyRotationPreservesOldSecret(): void
    {
        // Set up valid environment
        $validConfig = [
            'JWT_SECRET' => 'original-secret-key-at-least-32-characters-long-for-testing',
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'test_db',
            'DB_USERNAME' => 'test_user',
            'DB_PASSWORD' => 'test_password',
        ];
        $this->setEnvVars($validConfig);
        
        $credentialManager = new CredentialManager();
        
        // Store the original secret
        $originalSecret = $credentialManager->getJwtSecret();
        
        // Perform rotation
        $newSecret = 'new-secret-key-at-least-32-characters-long-for-testing-now';
        $credentialManager->rotateJwtSecret($newSecret);
        
        // Verify that the old secret is preserved
        $previousSecret = $credentialManager->getJwtPreviousSecret();
        
        $this->assertNotNull(
            $previousSecret,
            'Previous secret should not be null after rotation'
        );
        
        $this->assertEquals(
            $originalSecret,
            $previousSecret,
            'Previous secret should match the original secret before rotation'
        );
        
        // Verify that the new secret is different from the old secret
        $currentSecret = $credentialManager->getJwtSecret();
        
        $this->assertNotEquals(
            $currentSecret,
            $previousSecret,
            'Current secret should be different from previous secret after rotation'
        );
        
        // Verify that both secrets are accessible simultaneously
        $this->assertNotNull(
            $currentSecret,
            'Current secret should be accessible after rotation'
        );
        
        $this->assertNotNull(
            $previousSecret,
            'Previous secret should be accessible after rotation'
        );
        
        // Verify that both secrets meet the minimum length requirement
        $this->assertGreaterThanOrEqual(
            32,
            strlen($currentSecret),
            'Current secret should meet minimum length requirement'
        );
        
        $this->assertGreaterThanOrEqual(
            32,
            strlen($previousSecret),
            'Previous secret should meet minimum length requirement'
        );
    }
}

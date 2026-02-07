<?php

namespace tests\unit\controllers;

use common\components\security\RateLimitBehavior;
use common\components\security\RateLimiter;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Rate Limit integration into API controllers.
 *
 * Verifies that the RateLimiter component is properly registered in the API config
 * and that RateLimitBehavior is correctly configured in the key API controllers.
 *
 * Requirements: 4.1, 4.2
 *
 * @group security-rate-limit-integration
 */
class RateLimitIntegrationTest extends TestCase
{
    // =========================================================================
    // API Config: RateLimiter component registration
    // =========================================================================

    /**
     * Test that the API config registers the rateLimiter component.
     */
    public function testApiConfigRegistersRateLimiterComponent()
    {
        $config = require __DIR__ . '/../../../api/config/main.php';

        $this->assertArrayHasKey('components', $config);
        $this->assertArrayHasKey('rateLimiter', $config['components']);
        $this->assertEquals(
            'common\components\security\RateLimiter',
            $config['components']['rateLimiter']['class']
        );
    }

    /**
     * Test that the API config defines all three rate limit strategies.
     */
    public function testApiConfigDefinesAllStrategies()
    {
        $config = require __DIR__ . '/../../../api/config/main.php';

        $strategies = $config['components']['rateLimiter']['strategies'];

        $this->assertArrayHasKey('ip', $strategies);
        $this->assertArrayHasKey('user', $strategies);
        $this->assertArrayHasKey('login', $strategies);
    }

    /**
     * Test that the IP strategy is configured with 100 requests per 60 seconds.
     */
    public function testIpStrategyConfiguration()
    {
        $config = require __DIR__ . '/../../../api/config/main.php';

        $ipStrategy = $config['components']['rateLimiter']['strategies']['ip'];

        $this->assertEquals(100, $ipStrategy['limit']);
        $this->assertEquals(60, $ipStrategy['window']);
    }

    /**
     * Test that the user strategy is configured with 1000 requests per 3600 seconds.
     */
    public function testUserStrategyConfiguration()
    {
        $config = require __DIR__ . '/../../../api/config/main.php';

        $userStrategy = $config['components']['rateLimiter']['strategies']['user'];

        $this->assertEquals(1000, $userStrategy['limit']);
        $this->assertEquals(3600, $userStrategy['window']);
    }

    /**
     * Test that the login strategy is configured with 5 requests per 900 seconds.
     */
    public function testLoginStrategyConfiguration()
    {
        $config = require __DIR__ . '/../../../api/config/main.php';

        $loginStrategy = $config['components']['rateLimiter']['strategies']['login'];

        $this->assertEquals(5, $loginStrategy['limit']);
        $this->assertEquals(900, $loginStrategy['window']);
    }

    // =========================================================================
    // FileController (v1): RateLimitBehavior integration
    // =========================================================================

    /**
     * Test that v1 FileController includes RateLimitBehavior in its behaviors.
     */
    public function testV1FileControllerHasRateLimitBehavior()
    {
        $controllerSource = file_get_contents(
            __DIR__ . '/../../../api/modules/v1/controllers/FileController.php'
        );

        $this->assertStringContainsString(
            'RateLimitBehavior::class',
            $controllerSource,
            'FileController should reference RateLimitBehavior'
        );
        $this->assertStringContainsString(
            "'rateLimiter'",
            $controllerSource,
            'FileController should configure the rateLimiter behavior key'
        );
        $this->assertStringContainsString(
            "'defaultStrategy' => 'ip'",
            $controllerSource,
            'FileController should use the ip default strategy'
        );
    }

    /**
     * Test that v1 FileController imports the RateLimitBehavior class.
     */
    public function testV1FileControllerImportsRateLimitBehavior()
    {
        $controllerSource = file_get_contents(
            __DIR__ . '/../../../api/modules/v1/controllers/FileController.php'
        );

        $this->assertStringContainsString(
            'use common\components\security\RateLimitBehavior;',
            $controllerSource
        );
    }

    // =========================================================================
    // Legacy SiteController: RateLimitBehavior integration
    // =========================================================================

    /**
     * Test that legacy SiteController includes RateLimitBehavior with login strategy.
     */
    public function testLegacySiteControllerHasRateLimitBehavior()
    {
        $controllerSource = file_get_contents(
            __DIR__ . '/../../../api/controllers/SiteController.php'
        );

        $this->assertStringContainsString(
            'RateLimitBehavior::class',
            $controllerSource,
            'Legacy SiteController should reference RateLimitBehavior'
        );
        $this->assertStringContainsString(
            "'login' => 'login'",
            $controllerSource,
            'Legacy SiteController should map login action to login strategy'
        );
    }

    // =========================================================================
    // V1 SiteController: RateLimitBehavior integration
    // =========================================================================

    /**
     * Test that v1 SiteController includes RateLimitBehavior with login strategy.
     */
    public function testV1SiteControllerHasRateLimitBehavior()
    {
        $controllerSource = file_get_contents(
            __DIR__ . '/../../../api/modules/v1/controllers/SiteController.php'
        );

        $this->assertStringContainsString(
            'RateLimitBehavior::class',
            $controllerSource,
            'V1 SiteController should reference RateLimitBehavior'
        );
        $this->assertStringContainsString(
            "'login' => 'login'",
            $controllerSource,
            'V1 SiteController should map login action to login strategy'
        );
    }

    // =========================================================================
    // AuthController: RateLimitBehavior integration
    // =========================================================================

    /**
     * Test that AuthController includes RateLimitBehavior with login strategy.
     */
    public function testAuthControllerHasRateLimitBehavior()
    {
        $controllerSource = file_get_contents(
            __DIR__ . '/../../../api/modules/v1/controllers/AuthController.php'
        );

        $this->assertStringContainsString(
            'RateLimitBehavior::class',
            $controllerSource,
            'AuthController should reference RateLimitBehavior'
        );
        $this->assertStringContainsString(
            "'login' => 'login'",
            $controllerSource,
            'AuthController should map login action to login strategy'
        );
        $this->assertStringContainsString(
            "'defaultStrategy' => 'ip'",
            $controllerSource,
            'AuthController should use ip as default strategy'
        );
    }

    /**
     * Test that AuthController imports the RateLimitBehavior class.
     */
    public function testAuthControllerImportsRateLimitBehavior()
    {
        $controllerSource = file_get_contents(
            __DIR__ . '/../../../api/modules/v1/controllers/AuthController.php'
        );

        $this->assertStringContainsString(
            'use common\components\security\RateLimitBehavior;',
            $controllerSource
        );
    }

    // =========================================================================
    // RateLimiter component instantiation
    // =========================================================================

    /**
     * Test that the RateLimiter component can be instantiated with the config values.
     */
    public function testRateLimiterCanBeInstantiatedFromConfig()
    {
        $config = require __DIR__ . '/../../../api/config/main.php';
        $rateLimiterConfig = $config['components']['rateLimiter'];

        $rateLimiter = new RateLimiter();
        $rateLimiter->strategies = $rateLimiterConfig['strategies'];
        $rateLimiter->init();

        // Verify all strategies are accessible
        $ipStrategy = $rateLimiter->getStrategy('ip');
        $this->assertEquals(100, $ipStrategy['limit']);
        $this->assertEquals(60, $ipStrategy['window']);

        $userStrategy = $rateLimiter->getStrategy('user');
        $this->assertEquals(1000, $userStrategy['limit']);
        $this->assertEquals(3600, $userStrategy['window']);

        $loginStrategy = $rateLimiter->getStrategy('login');
        $this->assertEquals(5, $loginStrategy['limit']);
        $this->assertEquals(900, $loginStrategy['window']);
    }

    /**
     * Test that the RateLimitBehavior can be configured with the rateLimiter component ID.
     */
    public function testRateLimitBehaviorCanBeConfiguredWithComponentId()
    {
        $behavior = new RateLimitBehavior();
        $behavior->rateLimiter = 'rateLimiter';
        $behavior->defaultStrategy = 'ip';
        $behavior->actionStrategies = ['login' => 'login'];

        $this->assertEquals('rateLimiter', $behavior->rateLimiter);
        $this->assertEquals('ip', $behavior->defaultStrategy);
        $this->assertEquals(['login' => 'login'], $behavior->actionStrategies);
    }
}

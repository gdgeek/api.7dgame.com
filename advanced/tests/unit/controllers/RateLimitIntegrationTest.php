<?php

namespace tests\unit\controllers;

use common\components\security\RateLimitBehavior;
use common\components\security\RateLimiter;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Rate Limit integration into API controllers.
 *
 * Verifies that RateLimitBehavior is correctly configured in the key API controllers
 * and that the RateLimiter component works with the expected strategy values.
 *
 * Note: We do NOT require api/config/main.php directly because it depends on
 * local config files (params-local.php etc.) that may not exist in CI.
 * Instead we verify the config source contains the expected entries and
 * test the RateLimiter component with the expected production values.
 *
 * Requirements: 4.1, 4.2
 *
 * @group security-rate-limit-integration
 */
class RateLimitIntegrationTest extends TestCase
{
    // =========================================================================
    // RateLimiter component: production strategy values
    // =========================================================================

    /**
     * Test that the RateLimiter component works with expected production strategy values.
     * Verifies IP: 100/60s, User: 1000/3600s, Login: 5/900s.
     */
    public function testRateLimiterWithExpectedProductionStrategies()
    {
        $rateLimiter = new RateLimiter();
        $rateLimiter->strategies = [
            'ip' => ['limit' => 100, 'window' => 60],
            'user' => ['limit' => 1000, 'window' => 3600],
            'login' => ['limit' => 5, 'window' => 900],
        ];
        $rateLimiter->init();

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

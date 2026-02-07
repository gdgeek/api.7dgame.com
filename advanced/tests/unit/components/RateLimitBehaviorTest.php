<?php

namespace tests\unit\components;

use common\components\security\RateLimitBehavior;
use common\components\security\RateLimiter;
use PHPUnit\Framework\TestCase;
use yii\base\Action;
use yii\base\Module;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

/**
 * A testable subclass of RateLimitBehavior that allows injecting dependencies
 * without requiring a full Yii application context.
 */
class TestableRateLimitBehavior extends RateLimitBehavior
{
    /**
     * @var string|null Override identifier for testing.
     */
    public $testIdentifier;

    /**
     * @var Response|null Override response for testing.
     */
    public $testResponse;

    /**
     * {@inheritdoc}
     */
    protected function getIdentifier(): string
    {
        if ($this->testIdentifier !== null) {
            return $this->testIdentifier;
        }
        return parent::getIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveRateLimiter(): RateLimiter
    {
        // For testing, always use the directly-assigned instance
        if ($this->rateLimiter instanceof RateLimiter) {
            return $this->rateLimiter;
        }
        return parent::resolveRateLimiter();
    }
}

/**
 * A testable RateLimiter that allows controlling the current time.
 */
class BehaviorTestRateLimiter extends RateLimiter
{
    /**
     * @var float|null Overridden current time for testing.
     */
    public $testTime;

    /**
     * {@inheritdoc}
     */
    protected function getCurrentTime(): float
    {
        return $this->testTime ?? microtime(true);
    }
}

/**
 * Unit tests for the RateLimitBehavior component.
 *
 * Tests the Yii2 ActionFilter integration including:
 * - Rate limit headers (X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset)
 * - HTTP 429 response with Retry-After header
 * - Strategy selection per action
 * - Identifier determination (IP vs user)
 * - Request recording on allowed requests
 *
 * Requirements: 4.1, 4.2, 4.3
 *
 * @group security-rate-limit-behavior
 */
class RateLimitBehaviorTest extends TestCase
{
    /**
     * @var BehaviorTestRateLimiter
     */
    private $rateLimiter;

    /**
     * @var TestableRateLimitBehavior
     */
    private $behavior;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Action
     */
    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a RateLimiter with fixed time
        $this->rateLimiter = new BehaviorTestRateLimiter();
        $this->rateLimiter->strategies = [
            'ip' => ['limit' => 100, 'window' => 60],
            'user' => ['limit' => 1000, 'window' => 3600],
            'login' => ['limit' => 5, 'window' => 900],
        ];
        $this->rateLimiter->testTime = 1000000.0;
        $this->rateLimiter->init();

        // Create a mock response
        $this->response = new Response();

        // Create the behavior
        $this->behavior = new TestableRateLimitBehavior();
        $this->behavior->rateLimiter = $this->rateLimiter;
        $this->behavior->testIdentifier = '192.168.1.1';
        $this->behavior->testResponse = $this->response;

        // Create a mock action
        $this->action = $this->createMockAction('index');

        // Set up Yii::$app->response to our test response
        $this->setUpYiiResponse();
    }

    /**
     * Set up Yii::$app->response for testing.
     */
    private function setUpYiiResponse(): void
    {
        // We need Yii::$app->response to be available
        if (\Yii::$app !== null) {
            \Yii::$app->set('response', $this->response);
        }
    }

    /**
     * Create a mock Action object with the given ID.
     *
     * @param string $actionId
     * @return Action
     */
    private function createMockAction(string $actionId): Action
    {
        $module = $this->createMock(Module::class);
        $module->method('getUniqueId')->willReturn('test');

        $controller = $this->getMockBuilder(Controller::class)
            ->setConstructorArgs(['test-controller', $module])
            ->onlyMethods([])
            ->getMock();

        return new Action($actionId, $controller);
    }

    // =========================================================================
    // Rate limit headers tests
    // =========================================================================

    public function testSetsRateLimitHeadersOnAllowedRequest()
    {
        $result = $this->behavior->beforeAction($this->action);

        $this->assertTrue($result);

        $headers = $this->response->headers;
        $this->assertEquals('100', $headers->get('X-RateLimit-Limit'));
        $this->assertEquals('99', $headers->get('X-RateLimit-Remaining'));
        $this->assertNotNull($headers->get('X-RateLimit-Reset'));
    }

    public function testRateLimitRemainingDecrementsWithEachRequest()
    {
        // First request
        $this->behavior->beforeAction($this->action);
        $this->assertEquals('99', $this->response->headers->get('X-RateLimit-Remaining'));

        // Second request
        $this->response = new Response();
        $this->setUpYiiResponse();
        $this->behavior->beforeAction($this->action);
        $this->assertEquals('98', $this->response->headers->get('X-RateLimit-Remaining'));

        // Third request
        $this->response = new Response();
        $this->setUpYiiResponse();
        $this->behavior->beforeAction($this->action);
        $this->assertEquals('97', $this->response->headers->get('X-RateLimit-Remaining'));
    }

    public function testRateLimitResetHeaderIsUnixTimestamp()
    {
        $this->behavior->beforeAction($this->action);

        $resetTime = $this->response->headers->get('X-RateLimit-Reset');
        $this->assertNotNull($resetTime);
        $this->assertIsNumeric($resetTime);
        // Reset time should be in the future (relative to the test time)
        $this->assertGreaterThan(0, (int) $resetTime);
    }

    // =========================================================================
    // HTTP 429 response tests
    // =========================================================================

    public function testReturns429WhenRateLimitExceeded()
    {
        // Exhaust the IP limit (100 requests)
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('192.168.1.1', 'ip');
        }

        $result = $this->behavior->beforeAction($this->action);

        $this->assertFalse($result);
        $this->assertEquals(429, $this->response->statusCode);
    }

    public function testSetsRetryAfterHeaderWhenExceeded()
    {
        // Exhaust the IP limit
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('192.168.1.1', 'ip');
        }

        $this->behavior->beforeAction($this->action);

        $retryAfter = $this->response->headers->get('Retry-After');
        $this->assertNotNull($retryAfter);
        $this->assertIsNumeric($retryAfter);
        $this->assertGreaterThanOrEqual(1, (int) $retryAfter);
    }

    public function testReturnsJsonErrorBodyWhenExceeded()
    {
        // Exhaust the IP limit
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('192.168.1.1', 'ip');
        }

        $this->behavior->beforeAction($this->action);

        $this->assertEquals(Response::FORMAT_JSON, $this->response->format);
        $this->assertIsArray($this->response->data);
        $this->assertArrayHasKey('error', $this->response->data);
        $this->assertEquals('RATE_LIMIT_EXCEEDED', $this->response->data['error']['code']);
        $this->assertArrayHasKey('message', $this->response->data['error']);
        $this->assertArrayHasKey('retry_after', $this->response->data['error']);
    }

    public function testRateLimitHeadersSetEvenWhenExceeded()
    {
        // Exhaust the IP limit
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('192.168.1.1', 'ip');
        }

        $this->behavior->beforeAction($this->action);

        $headers = $this->response->headers;
        $this->assertEquals('100', $headers->get('X-RateLimit-Limit'));
        $this->assertEquals('0', $headers->get('X-RateLimit-Remaining'));
        $this->assertNotNull($headers->get('X-RateLimit-Reset'));
    }

    // =========================================================================
    // Strategy selection tests
    // =========================================================================

    public function testUsesDefaultStrategyWhenNoActionMapping()
    {
        $this->behavior->defaultStrategy = 'ip';
        $this->behavior->actionStrategies = [];

        $this->behavior->beforeAction($this->action);

        // IP strategy has limit 100
        $this->assertEquals('100', $this->response->headers->get('X-RateLimit-Limit'));
    }

    public function testUsesActionSpecificStrategy()
    {
        $this->behavior->actionStrategies = ['login' => 'login'];

        $loginAction = $this->createMockAction('login');
        $this->behavior->beforeAction($loginAction);

        // Login strategy has limit 5
        $this->assertEquals('5', $this->response->headers->get('X-RateLimit-Limit'));
    }

    public function testFallsBackToDefaultWhenActionNotMapped()
    {
        $this->behavior->defaultStrategy = 'user';
        $this->behavior->actionStrategies = ['login' => 'login'];

        // 'index' action is not mapped, should use 'user' default
        $this->behavior->beforeAction($this->action);

        // User strategy has limit 1000
        $this->assertEquals('1000', $this->response->headers->get('X-RateLimit-Limit'));
    }

    public function testMultipleActionStrategies()
    {
        $this->behavior->actionStrategies = [
            'login' => 'login',
            'register' => 'login',
            'search' => 'ip',
        ];

        // Test login action
        $loginAction = $this->createMockAction('login');
        $this->behavior->beforeAction($loginAction);
        $this->assertEquals('5', $this->response->headers->get('X-RateLimit-Limit'));

        // Test search action
        $this->response = new Response();
        $this->setUpYiiResponse();
        $searchAction = $this->createMockAction('search');
        $this->behavior->beforeAction($searchAction);
        $this->assertEquals('100', $this->response->headers->get('X-RateLimit-Limit'));
    }

    // =========================================================================
    // Request recording tests
    // =========================================================================

    public function testRecordsRequestWhenAllowed()
    {
        $remaining = $this->rateLimiter->getRemainingRequests('192.168.1.1', 'ip');
        $this->assertEquals(100, $remaining);

        $this->behavior->beforeAction($this->action);

        $remaining = $this->rateLimiter->getRemainingRequests('192.168.1.1', 'ip');
        $this->assertEquals(99, $remaining);
    }

    public function testDoesNotRecordRequestWhenBlocked()
    {
        // Exhaust the limit
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('192.168.1.1', 'ip');
        }

        $remaining = $this->rateLimiter->getRemainingRequests('192.168.1.1', 'ip');
        $this->assertEquals(0, $remaining);

        // This should NOT record another request
        $this->behavior->beforeAction($this->action);

        // Still 0 remaining (no extra recording)
        $remaining = $this->rateLimiter->getRemainingRequests('192.168.1.1', 'ip');
        $this->assertEquals(0, $remaining);
    }

    // =========================================================================
    // Identifier tests
    // =========================================================================

    public function testDifferentIdentifiersHaveIndependentLimits()
    {
        // Exhaust limit for identifier 1
        $this->behavior->testIdentifier = '10.0.0.1';
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('10.0.0.1', 'ip');
        }

        $result1 = $this->behavior->beforeAction($this->action);
        $this->assertFalse($result1);

        // Identifier 2 should still be allowed
        $this->behavior->testIdentifier = '10.0.0.2';
        $this->response = new Response();
        $this->setUpYiiResponse();

        $result2 = $this->behavior->beforeAction($this->action);
        $this->assertTrue($result2);
    }

    // =========================================================================
    // RateLimiter resolution tests
    // =========================================================================

    public function testAcceptsRateLimiterInstance()
    {
        $behavior = new TestableRateLimitBehavior();
        $behavior->rateLimiter = $this->rateLimiter;
        $behavior->testIdentifier = '1.2.3.4';

        $result = $behavior->beforeAction($this->action);
        $this->assertTrue($result);
    }

    public function testThrowsExceptionForInvalidRateLimiterType()
    {
        $behavior = new TestableRateLimitBehavior();
        $behavior->rateLimiter = 12345; // invalid type
        $behavior->testIdentifier = '1.2.3.4';

        $this->expectException(\yii\base\InvalidConfigException::class);
        $behavior->beforeAction($this->action);
    }

    // =========================================================================
    // Login strategy specific tests (Requirement 4.3)
    // =========================================================================

    public function testLoginStrategyBlocksAfter5Attempts()
    {
        $this->behavior->actionStrategies = ['login' => 'login'];
        $loginAction = $this->createMockAction('login');

        // Make 5 successful requests
        for ($i = 0; $i < 5; $i++) {
            $this->response = new Response();
            $this->setUpYiiResponse();
            $result = $this->behavior->beforeAction($loginAction);
            $this->assertTrue($result, "Request {$i} should be allowed");
        }

        // 6th request should be blocked
        $this->response = new Response();
        $this->setUpYiiResponse();
        $result = $this->behavior->beforeAction($loginAction);
        $this->assertFalse($result);
        $this->assertEquals(429, $this->response->statusCode);
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function testRetryAfterIsAtLeastOne()
    {
        // Exhaust the limit
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('192.168.1.1', 'ip');
        }

        $this->behavior->beforeAction($this->action);

        $retryAfter = (int) $this->response->headers->get('Retry-After');
        $this->assertGreaterThanOrEqual(1, $retryAfter);
    }

    public function testAllowedRequestReturnsTrueFromBeforeAction()
    {
        $result = $this->behavior->beforeAction($this->action);
        $this->assertTrue($result);
    }

    public function testBlockedRequestReturnsFalseFromBeforeAction()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('192.168.1.1', 'ip');
        }

        $result = $this->behavior->beforeAction($this->action);
        $this->assertFalse($result);
    }

    public function testRemainingIsZeroWhenExceeded()
    {
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest('192.168.1.1', 'ip');
        }

        $this->behavior->beforeAction($this->action);

        $this->assertEquals('0', $this->response->headers->get('X-RateLimit-Remaining'));
    }

    public function testDefaultStrategyIsIp()
    {
        $behavior = new RateLimitBehavior();
        $this->assertEquals('ip', $behavior->defaultStrategy);
    }

    public function testDefaultActionStrategiesIsEmpty()
    {
        $behavior = new RateLimitBehavior();
        $this->assertEmpty($behavior->actionStrategies);
    }
}

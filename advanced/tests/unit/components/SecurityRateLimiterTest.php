<?php

namespace tests\unit\components;

use common\components\security\InMemoryRateLimiterStorage;
use common\components\security\RateLimiter;
use PHPUnit\Framework\TestCase;

/**
 * A testable subclass of RateLimiter that allows controlling the current time.
 */
class TestableRateLimiter extends RateLimiter
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

    /**
     * Advance the test clock by the given number of seconds.
     *
     * @param float $seconds
     */
    public function advanceTime(float $seconds): void
    {
        if ($this->testTime === null) {
            $this->testTime = microtime(true);
        }
        $this->testTime += $seconds;
    }
}

/**
 * Unit tests for the security RateLimiter component.
 *
 * Tests the sliding window algorithm, configurable strategies,
 * and the RateLimiterInterface methods (checkLimit, recordRequest,
 * getRemainingRequests, getResetTime).
 *
 * @group security-rate-limiter
 */
class SecurityRateLimiterTest extends TestCase
{
    /**
     * @var TestableRateLimiter
     */
    private $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rateLimiter = new TestableRateLimiter();
        $this->rateLimiter->strategies = [
            'ip' => ['limit' => 100, 'window' => 60],
            'user' => ['limit' => 1000, 'window' => 3600],
            'login' => ['limit' => 5, 'window' => 900],
        ];
        $this->rateLimiter->testTime = 1000000.0; // Fixed start time
        $this->rateLimiter->init();
    }

    // =========================================================================
    // checkLimit tests
    // =========================================================================

    public function testCheckLimitAllowsRequestsUnderLimit()
    {
        // No requests recorded yet — should be allowed
        $this->assertTrue($this->rateLimiter->checkLimit('192.168.1.1', 'ip'));
    }

    public function testCheckLimitBlocksRequestsAtLimit()
    {
        $ip = '10.0.0.1';

        // Record exactly 100 requests (the IP limit)
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest($ip, 'ip');
        }

        // The 101st should be blocked
        $this->assertFalse($this->rateLimiter->checkLimit($ip, 'ip'));
    }

    public function testCheckLimitAllowsAfterWindowExpires()
    {
        $ip = '10.0.0.2';

        // Fill up the limit
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest($ip, 'ip');
        }
        $this->assertFalse($this->rateLimiter->checkLimit($ip, 'ip'));

        // Advance time past the window (60 seconds + 1)
        $this->rateLimiter->advanceTime(61);

        // Should be allowed again
        $this->assertTrue($this->rateLimiter->checkLimit($ip, 'ip'));
    }

    public function testCheckLimitSlidingWindowPartialExpiry()
    {
        $ip = '10.0.0.3';

        // Record 50 requests at time T
        for ($i = 0; $i < 50; $i++) {
            $this->rateLimiter->recordRequest($ip, 'ip');
        }

        // Advance 30 seconds and record 50 more
        $this->rateLimiter->advanceTime(30);
        for ($i = 0; $i < 50; $i++) {
            $this->rateLimiter->recordRequest($ip, 'ip');
        }

        // At T+30, all 100 are within the window — should be blocked
        $this->assertFalse($this->rateLimiter->checkLimit($ip, 'ip'));

        // Advance to T+61 — the first 50 expire, only 50 remain
        $this->rateLimiter->advanceTime(31);
        $this->assertTrue($this->rateLimiter->checkLimit($ip, 'ip'));
        $this->assertEquals(50, $this->rateLimiter->getRemainingRequests($ip, 'ip'));
    }

    // =========================================================================
    // Strategy-specific tests
    // =========================================================================

    public function testIpRateLimitStrategy()
    {
        $ip = '192.168.0.1';

        // Default IP strategy: 100 requests / 60 seconds
        $this->assertEquals(100, $this->rateLimiter->getRemainingRequests($ip, 'ip'));

        $this->rateLimiter->recordRequest($ip, 'ip');
        $this->assertEquals(99, $this->rateLimiter->getRemainingRequests($ip, 'ip'));
    }

    public function testUserRateLimitStrategy()
    {
        $userId = 'user_42';

        // Default user strategy: 1000 requests / 3600 seconds
        $this->assertEquals(1000, $this->rateLimiter->getRemainingRequests($userId, 'user'));

        for ($i = 0; $i < 10; $i++) {
            $this->rateLimiter->recordRequest($userId, 'user');
        }
        $this->assertEquals(990, $this->rateLimiter->getRemainingRequests($userId, 'user'));
    }

    public function testLoginRateLimitStrategy()
    {
        $ip = '10.10.10.10';

        // Default login strategy: 5 requests / 900 seconds (15 min)
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($this->rateLimiter->checkLimit($ip, 'login'));
            $this->rateLimiter->recordRequest($ip, 'login');
        }

        // 6th attempt should be blocked
        $this->assertFalse($this->rateLimiter->checkLimit($ip, 'login'));
        $this->assertEquals(0, $this->rateLimiter->getRemainingRequests($ip, 'login'));
    }

    public function testLoginLimitResetsAfter15Minutes()
    {
        $ip = '10.10.10.11';

        // Exhaust login limit
        for ($i = 0; $i < 5; $i++) {
            $this->rateLimiter->recordRequest($ip, 'login');
        }
        $this->assertFalse($this->rateLimiter->checkLimit($ip, 'login'));

        // Advance 15 minutes + 1 second
        $this->rateLimiter->advanceTime(901);

        $this->assertTrue($this->rateLimiter->checkLimit($ip, 'login'));
        $this->assertEquals(5, $this->rateLimiter->getRemainingRequests($ip, 'login'));
    }

    // =========================================================================
    // recordRequest tests
    // =========================================================================

    public function testRecordRequestIncrementsCount()
    {
        $ip = '1.2.3.4';

        $this->assertEquals(100, $this->rateLimiter->getRemainingRequests($ip, 'ip'));

        $this->rateLimiter->recordRequest($ip, 'ip');
        $this->assertEquals(99, $this->rateLimiter->getRemainingRequests($ip, 'ip'));

        $this->rateLimiter->recordRequest($ip, 'ip');
        $this->assertEquals(98, $this->rateLimiter->getRemainingRequests($ip, 'ip'));
    }

    // =========================================================================
    // getRemainingRequests tests
    // =========================================================================

    public function testGetRemainingRequestsNeverNegative()
    {
        $ip = '5.6.7.8';

        // Record more than the limit
        for ($i = 0; $i < 110; $i++) {
            $this->rateLimiter->recordRequest($ip, 'ip');
        }

        $this->assertEquals(0, $this->rateLimiter->getRemainingRequests($ip, 'ip'));
    }

    public function testGetRemainingRequestsFullWhenNoRequests()
    {
        $this->assertEquals(100, $this->rateLimiter->getRemainingRequests('new_ip', 'ip'));
        $this->assertEquals(1000, $this->rateLimiter->getRemainingRequests('new_user', 'user'));
        $this->assertEquals(5, $this->rateLimiter->getRemainingRequests('new_login', 'login'));
    }

    // =========================================================================
    // getResetTime tests
    // =========================================================================

    public function testGetResetTimeWithNoEntries()
    {
        $resetTime = $this->rateLimiter->getResetTime('fresh_ip', 'ip');
        // Should be current time + window
        $expected = 1000000.0 + 60;
        $this->assertEquals($expected, $resetTime);
    }

    public function testGetResetTimeWithEntries()
    {
        $ip = '9.8.7.6';

        $this->rateLimiter->recordRequest($ip, 'ip');
        $resetTime = $this->rateLimiter->getResetTime($ip, 'ip');

        // Reset time = oldest entry timestamp + window
        $expected = 1000000.0 + 60;
        $this->assertEquals($expected, $resetTime);
    }

    public function testGetResetTimeUpdatesAsEntriesExpire()
    {
        $ip = '11.22.33.44';

        // Record at T=1000000
        $this->rateLimiter->recordRequest($ip, 'ip');

        // Advance 30 seconds and record another
        $this->rateLimiter->advanceTime(30);
        $this->rateLimiter->recordRequest($ip, 'ip');

        // Reset time should be based on oldest entry (T=1000000 + 60 = 1000060)
        $this->assertEquals(1000060.0, $this->rateLimiter->getResetTime($ip, 'ip'));

        // Advance to T+61 — first entry expires
        $this->rateLimiter->advanceTime(31);
        // Now oldest is T+30 = 1000030, reset = 1000030 + 60 = 1000090
        $this->assertEquals(1000090.0, $this->rateLimiter->getResetTime($ip, 'ip'));
    }

    // =========================================================================
    // Strategy configuration tests
    // =========================================================================

    public function testCustomStrategyCanBeAdded()
    {
        $this->rateLimiter->setStrategy('api_search', 50, 120);

        $this->assertEquals(50, $this->rateLimiter->getRemainingRequests('client1', 'api_search'));

        for ($i = 0; $i < 50; $i++) {
            $this->rateLimiter->recordRequest('client1', 'api_search');
        }

        $this->assertFalse($this->rateLimiter->checkLimit('client1', 'api_search'));
    }

    public function testStrategyCanBeUpdatedAtRuntime()
    {
        $ip = '1.1.1.1';

        // Start with default IP limit of 100
        $this->assertEquals(100, $this->rateLimiter->getRemainingRequests($ip, 'ip'));

        // Update to 200
        $this->rateLimiter->setStrategy('ip', 200, 60);
        $this->assertEquals(200, $this->rateLimiter->getRemainingRequests($ip, 'ip'));
    }

    public function testUnknownStrategyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown rate limit strategy: nonexistent');

        $this->rateLimiter->checkLimit('id', 'nonexistent');
    }

    public function testStrategyMissingLimitThrowsException()
    {
        $this->rateLimiter->strategies['broken'] = ['window' => 60];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("must define 'limit' and 'window'");

        $this->rateLimiter->checkLimit('id', 'broken');
    }

    public function testStrategyMissingWindowThrowsException()
    {
        $this->rateLimiter->strategies['broken2'] = ['limit' => 10];

        $this->expectException(\InvalidArgumentException::class);
        $this->rateLimiter->checkLimit('id', 'broken2');
    }

    // =========================================================================
    // Isolation tests
    // =========================================================================

    public function testDifferentIdentifiersAreIsolated()
    {
        $ip1 = '10.0.0.1';
        $ip2 = '10.0.0.2';

        // Exhaust limit for ip1
        for ($i = 0; $i < 100; $i++) {
            $this->rateLimiter->recordRequest($ip1, 'ip');
        }

        $this->assertFalse($this->rateLimiter->checkLimit($ip1, 'ip'));
        $this->assertTrue($this->rateLimiter->checkLimit($ip2, 'ip'));
        $this->assertEquals(100, $this->rateLimiter->getRemainingRequests($ip2, 'ip'));
    }

    public function testDifferentActionsAreIsolated()
    {
        $id = 'shared_id';

        // Exhaust login limit (5)
        for ($i = 0; $i < 5; $i++) {
            $this->rateLimiter->recordRequest($id, 'login');
        }

        $this->assertFalse($this->rateLimiter->checkLimit($id, 'login'));
        // IP action should still be fully available
        $this->assertTrue($this->rateLimiter->checkLimit($id, 'ip'));
        $this->assertEquals(100, $this->rateLimiter->getRemainingRequests($id, 'ip'));
    }

    // =========================================================================
    // Reset tests
    // =========================================================================

    public function testResetClearsAllEntriesForIdentifierAndAction()
    {
        $ip = '172.16.0.1';

        for ($i = 0; $i < 50; $i++) {
            $this->rateLimiter->recordRequest($ip, 'ip');
        }
        $this->assertEquals(50, $this->rateLimiter->getRemainingRequests($ip, 'ip'));

        $this->rateLimiter->reset($ip, 'ip');
        $this->assertEquals(100, $this->rateLimiter->getRemainingRequests($ip, 'ip'));
    }

    public function testResetDoesNotAffectOtherIdentifiers()
    {
        $ip1 = '172.16.0.1';
        $ip2 = '172.16.0.2';

        $this->rateLimiter->recordRequest($ip1, 'ip');
        $this->rateLimiter->recordRequest($ip2, 'ip');

        $this->rateLimiter->reset($ip1, 'ip');

        $this->assertEquals(100, $this->rateLimiter->getRemainingRequests($ip1, 'ip'));
        $this->assertEquals(99, $this->rateLimiter->getRemainingRequests($ip2, 'ip'));
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function testBoundaryAtExactLimit()
    {
        $ip = '10.0.0.99';

        // Record exactly limit - 1 requests
        for ($i = 0; $i < 99; $i++) {
            $this->rateLimiter->recordRequest($ip, 'ip');
        }

        // Should still be allowed (99 < 100)
        $this->assertTrue($this->rateLimiter->checkLimit($ip, 'ip'));
        $this->assertEquals(1, $this->rateLimiter->getRemainingRequests($ip, 'ip'));

        // Record the 100th
        $this->rateLimiter->recordRequest($ip, 'ip');

        // Now at limit — should be blocked
        $this->assertFalse($this->rateLimiter->checkLimit($ip, 'ip'));
        $this->assertEquals(0, $this->rateLimiter->getRemainingRequests($ip, 'ip'));
    }

    public function testStrategyWithLimitOfOne()
    {
        $this->rateLimiter->setStrategy('strict', 1, 30);

        $this->assertTrue($this->rateLimiter->checkLimit('client', 'strict'));
        $this->rateLimiter->recordRequest('client', 'strict');
        $this->assertFalse($this->rateLimiter->checkLimit('client', 'strict'));
    }

    public function testEmptyIdentifierWorks()
    {
        // Empty string is a valid identifier
        $this->assertTrue($this->rateLimiter->checkLimit('', 'ip'));
        $this->rateLimiter->recordRequest('', 'ip');
        $this->assertEquals(99, $this->rateLimiter->getRemainingRequests('', 'ip'));
    }

    // =========================================================================
    // Storage backend tests
    // =========================================================================

    public function testDefaultStorageIsInMemory()
    {
        $limiter = new RateLimiter();
        $limiter->init();
        $this->assertInstanceOf(InMemoryRateLimiterStorage::class, $limiter->getStorage());
    }

    public function testStorageCanBeSwapped()
    {
        $customStorage = new InMemoryRateLimiterStorage();
        $this->rateLimiter->setStorage($customStorage);
        $this->assertSame($customStorage, $this->rateLimiter->getStorage());
    }

    // =========================================================================
    // InMemoryRateLimiterStorage unit tests
    // =========================================================================

    public function testInMemoryStorageAddAndCount()
    {
        $storage = new InMemoryRateLimiterStorage();

        $this->assertEquals(0, $storage->count('key1'));

        $storage->add('key1', 100.0);
        $this->assertEquals(1, $storage->count('key1'));

        $storage->add('key1', 101.0);
        $this->assertEquals(2, $storage->count('key1'));
    }

    public function testInMemoryStoragePurgeExpired()
    {
        $storage = new InMemoryRateLimiterStorage();

        $storage->add('key1', 100.0);
        $storage->add('key1', 200.0);
        $storage->add('key1', 300.0);

        // Purge entries older than 200
        $storage->purgeExpired('key1', 200.0);

        // Only entries >= 200 remain
        $this->assertEquals(2, $storage->count('key1'));
    }

    public function testInMemoryStorageGetOldest()
    {
        $storage = new InMemoryRateLimiterStorage();

        $this->assertNull($storage->getOldest('key1'));

        $storage->add('key1', 300.0);
        $storage->add('key1', 100.0);
        $storage->add('key1', 200.0);

        $this->assertEquals(100.0, $storage->getOldest('key1'));
    }

    public function testInMemoryStorageClear()
    {
        $storage = new InMemoryRateLimiterStorage();

        $storage->add('key1', 100.0);
        $storage->add('key1', 200.0);
        $this->assertEquals(2, $storage->count('key1'));

        $storage->clear('key1');
        $this->assertEquals(0, $storage->count('key1'));
    }

    public function testInMemoryStoragePurgeRemovesEmptyKeys()
    {
        $storage = new InMemoryRateLimiterStorage();

        $storage->add('key1', 100.0);
        $storage->purgeExpired('key1', 200.0);

        // After purging all entries, count should be 0
        $this->assertEquals(0, $storage->count('key1'));
        $this->assertNull($storage->getOldest('key1'));
    }

    public function testInMemoryStorageIsolatesDifferentKeys()
    {
        $storage = new InMemoryRateLimiterStorage();

        $storage->add('key1', 100.0);
        $storage->add('key2', 200.0);

        $this->assertEquals(1, $storage->count('key1'));
        $this->assertEquals(1, $storage->count('key2'));

        $storage->clear('key1');
        $this->assertEquals(0, $storage->count('key1'));
        $this->assertEquals(1, $storage->count('key2'));
    }
}

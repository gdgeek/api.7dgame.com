<?php

namespace common\components\security;

use yii\base\Component;

/**
 * RateLimiter Component
 *
 * Implements rate limiting using a sliding window algorithm.
 * Supports configurable rate limit strategies for different actions:
 *   - IP-based rate limiting (100 requests/minute)
 *   - Authenticated user rate limiting (1000 requests/hour)
 *   - Login endpoint special limiting (5 requests/15 minutes)
 *
 * Uses a pluggable storage backend. Defaults to in-memory (array-based)
 * storage which can be swapped for Redis in production via configuration.
 *
 * Implements Requirements 4.1, 4.2 from backend-security-hardening spec.
 *
 * @author Kiro AI
 * @since 1.0
 */
class RateLimiter extends Component
{
    /**
     * @var array Named rate limit strategies.
     * Each strategy defines 'limit' (max requests) and 'window' (seconds).
     */
    public $strategies = [
        'ip' => [
            'limit' => 100,
            'window' => 60,       // 1 minute
        ],
        'user' => [
            'limit' => 1000,
            'window' => 3600,     // 1 hour
        ],
        'login' => [
            'limit' => 5,
            'window' => 900,      // 15 minutes
        ],
    ];

    /**
     * @var string The storage backend class to use.
     * Must implement RateLimiterStorageInterface.
     * Defaults to in-memory storage.
     */
    public $storageClass = InMemoryRateLimiterStorage::class;

    /**
     * @var array Configuration options passed to the storage backend constructor.
     */
    public $storageConfig = [];

    /**
     * @var RateLimiterStorageInterface The storage backend instance.
     */
    private $_storage;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->_storage = $this->createStorage();
    }

    /**
     * Create the storage backend instance.
     *
     * @return RateLimiterStorageInterface
     */
    protected function createStorage(): RateLimiterStorageInterface
    {
        $class = $this->storageClass;
        return new $class($this->storageConfig);
    }

    /**
     * Get the storage backend (for testing purposes).
     *
     * @return RateLimiterStorageInterface
     */
    public function getStorage(): RateLimiterStorageInterface
    {
        return $this->_storage;
    }

    /**
     * Set the storage backend (for testing purposes).
     *
     * @param RateLimiterStorageInterface $storage
     */
    public function setStorage(RateLimiterStorageInterface $storage): void
    {
        $this->_storage = $storage;
    }

    // =========================================================================
    // RateLimiterInterface methods
    // =========================================================================

    /**
     * Check whether the identifier has exceeded the rate limit for the given action.
     *
     * Uses a sliding window algorithm: counts the number of recorded timestamps
     * within the current window and compares against the configured limit.
     *
     * @param string $identifier Unique identifier (IP address, user ID, etc.)
     * @param string $action     The action / strategy name (e.g. 'ip', 'user', 'login')
     * @return bool True if the request is allowed (under limit), false if rate limit exceeded.
     */
    public function checkLimit(string $identifier, string $action): bool
    {
        $strategy = $this->getStrategy($action);
        $key = $this->buildKey($identifier, $action);
        $now = $this->getCurrentTime();
        $windowStart = $now - $strategy['window'];

        // Purge expired entries and count remaining
        $this->_storage->purgeExpired($key, $windowStart);
        $count = $this->_storage->count($key);

        return $count < $strategy['limit'];
    }

    /**
     * Record a request for the given identifier and action.
     *
     * Adds the current timestamp to the sliding window.
     *
     * @param string $identifier Unique identifier
     * @param string $action     The action / strategy name
     */
    public function recordRequest(string $identifier, string $action): void
    {
        $strategy = $this->getStrategy($action);
        $key = $this->buildKey($identifier, $action);
        $now = $this->getCurrentTime();
        $windowStart = $now - $strategy['window'];

        // Purge expired entries first
        $this->_storage->purgeExpired($key, $windowStart);

        // Add the new timestamp
        $this->_storage->add($key, $now);
    }

    /**
     * Get the number of remaining requests allowed for the identifier and action.
     *
     * @param string $identifier Unique identifier
     * @param string $action     The action / strategy name
     * @return int Number of remaining requests (>= 0)
     */
    public function getRemainingRequests(string $identifier, string $action): int
    {
        $strategy = $this->getStrategy($action);
        $key = $this->buildKey($identifier, $action);
        $now = $this->getCurrentTime();
        $windowStart = $now - $strategy['window'];

        $this->_storage->purgeExpired($key, $windowStart);
        $count = $this->_storage->count($key);

        return max(0, $strategy['limit'] - $count);
    }

    /**
     * Get the time (Unix timestamp) when the rate limit window resets.
     *
     * This is the time when the oldest entry in the current window expires,
     * or the current time + window if there are no entries.
     *
     * @param string $identifier Unique identifier
     * @param string $action     The action / strategy name
     * @return int Unix timestamp when the window resets
     */
    public function getResetTime(string $identifier, string $action): int
    {
        $strategy = $this->getStrategy($action);
        $key = $this->buildKey($identifier, $action);
        $now = $this->getCurrentTime();
        $windowStart = $now - $strategy['window'];

        $this->_storage->purgeExpired($key, $windowStart);

        $oldest = $this->_storage->getOldest($key);
        if ($oldest !== null) {
            return (int)$oldest + $strategy['window'];
        }

        return $now + $strategy['window'];
    }

    // =========================================================================
    // Strategy helpers
    // =========================================================================

    /**
     * Get the strategy configuration for the given action.
     *
     * @param string $action Strategy name
     * @return array ['limit' => int, 'window' => int]
     * @throws \InvalidArgumentException if the strategy is not defined
     */
    public function getStrategy(string $action): array
    {
        if (!isset($this->strategies[$action])) {
            throw new \InvalidArgumentException("Unknown rate limit strategy: {$action}");
        }

        $strategy = $this->strategies[$action];

        if (!isset($strategy['limit']) || !isset($strategy['window'])) {
            throw new \InvalidArgumentException(
                "Rate limit strategy '{$action}' must define 'limit' and 'window'"
            );
        }

        return $strategy;
    }

    /**
     * Add or update a rate limit strategy at runtime.
     *
     * @param string $name   Strategy name
     * @param int    $limit  Maximum number of requests
     * @param int    $window Time window in seconds
     */
    public function setStrategy(string $name, int $limit, int $window): void
    {
        $this->strategies[$name] = [
            'limit' => $limit,
            'window' => $window,
        ];
    }

    /**
     * Clear all recorded requests for a given identifier and action.
     *
     * @param string $identifier Unique identifier
     * @param string $action     The action / strategy name
     */
    public function reset(string $identifier, string $action): void
    {
        $key = $this->buildKey($identifier, $action);
        $this->_storage->clear($key);
    }

    // =========================================================================
    // Internal helpers
    // =========================================================================

    /**
     * Build a storage key from identifier and action.
     *
     * @param string $identifier
     * @param string $action
     * @return string
     */
    protected function buildKey(string $identifier, string $action): string
    {
        return "rate_limit:{$action}:{$identifier}";
    }

    /**
     * Get the current time as a float (microtime).
     * Extracted to a method so it can be overridden in tests.
     *
     * @return float
     */
    protected function getCurrentTime(): float
    {
        return microtime(true);
    }
}

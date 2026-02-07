<?php

namespace common\components\security;

/**
 * In-memory (array-based) storage backend for the RateLimiter.
 *
 * Stores request timestamps in PHP arrays. Suitable for testing and
 * single-process environments. For production with multiple workers,
 * use a Redis-backed storage instead.
 */
class InMemoryRateLimiterStorage implements RateLimiterStorageInterface
{
    /**
     * @var array<string, float[]> Stored timestamps keyed by rate limit key.
     */
    private $data = [];

    /**
     * Constructor.
     *
     * @param array $config Optional configuration (unused for in-memory storage)
     */
    public function __construct(array $config = [])
    {
        // No configuration needed for in-memory storage
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $key, float $timestamp): void
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = [];
        }
        $this->data[$key][] = $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function count(string $key): int
    {
        if (!isset($this->data[$key])) {
            return 0;
        }
        return count($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function purgeExpired(string $key, float $threshold): void
    {
        if (!isset($this->data[$key])) {
            return;
        }

        $this->data[$key] = array_values(
            array_filter($this->data[$key], function (float $ts) use ($threshold) {
                return $ts >= $threshold;
            })
        );

        // Clean up empty keys
        if (empty($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOldest(string $key): ?float
    {
        if (!isset($this->data[$key]) || empty($this->data[$key])) {
            return null;
        }

        return min($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(string $key): void
    {
        unset($this->data[$key]);
    }
}

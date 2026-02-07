<?php

namespace common\components\security;

/**
 * Interface for rate limiter storage backends.
 *
 * Implementations store timestamped entries per key and support
 * the sliding window algorithm by purging expired entries.
 */
interface RateLimiterStorageInterface
{
    /**
     * Add a timestamp entry for the given key.
     *
     * @param string $key       The storage key
     * @param float  $timestamp The timestamp to record
     */
    public function add(string $key, float $timestamp): void;

    /**
     * Count the number of entries for the given key.
     *
     * @param string $key The storage key
     * @return int Number of entries
     */
    public function count(string $key): int;

    /**
     * Remove all entries older than the given threshold.
     *
     * @param string $key       The storage key
     * @param float  $threshold Entries with timestamps < threshold are removed
     */
    public function purgeExpired(string $key, float $threshold): void;

    /**
     * Get the oldest timestamp entry for the given key.
     *
     * @param string $key The storage key
     * @return float|null The oldest timestamp, or null if no entries exist
     */
    public function getOldest(string $key): ?float;

    /**
     * Clear all entries for the given key.
     *
     * @param string $key The storage key
     */
    public function clear(string $key): void;
}

<?php

namespace common\components\security;

/**
 * Optional capability for storage backends that can atomically consume a
 * sliding-window allowance. Existing rate-limit strategies may continue to
 * use RateLimiterStorageInterface; the QR login-code issuer requires this
 * stronger operation.
 */
interface AtomicRateLimiterStorageInterface extends RateLimiterStorageInterface
{
    /**
     * @return array{allowed: bool, remaining: int, reset_at: int, retry_after: int}
     */
    public function consume(string $key, int $limit, int $windowSeconds): array;
}

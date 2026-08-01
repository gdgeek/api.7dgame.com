<?php

namespace common\components\security;

use RuntimeException;
use Yii;

/**
 * Shared Redis storage for the login-code issuance limiter.
 *
 * consume() uses one key and one EVAL invocation, so multiple PHP workers
 * cannot observe the same remaining slot and both claim it. The Lua script is
 * intentionally independent from the login-code Code_Record namespace.
 */
class RedisSlidingWindowRateLimiterStorage implements AtomicRateLimiterStorageInterface
{
    private const CONSUME_LUA = <<<'LUA'
local now = redis.call('TIME')
local now_ms = tonumber(now[1]) * 1000 + math.floor(tonumber(now[2]) / 1000)
local window_ms = tonumber(ARGV[1])
local limit = tonumber(ARGV[2])
local nonce = ARGV[3]
local cutoff = now_ms - window_ms

redis.call('ZREMRANGEBYSCORE', KEYS[1], '-inf', cutoff)
local count = tonumber(redis.call('ZCARD', KEYS[1]))
local allowed = 0

if count < limit then
    local member = tostring(now_ms) .. ':' .. nonce
    redis.call('ZADD', KEYS[1], now_ms, member)
    count = count + 1
    allowed = 1
end

if allowed == 1 then
    redis.call('PEXPIRE', KEYS[1], window_ms)
end

local oldest = redis.call('ZRANGE', KEYS[1], 0, 0, 'WITHSCORES')
local reset_ms = now_ms + window_ms
if oldest[2] ~= nil then
    reset_ms = tonumber(oldest[2]) + window_ms
end

local remaining = limit - count
if remaining < 0 then
    remaining = 0
end

local retry_after = math.ceil((reset_ms - now_ms) / 1000)
if retry_after < 1 then
    retry_after = 1
end

return {allowed, remaining, math.ceil(reset_ms / 1000), retry_after}
LUA;

    /** @var mixed|null */
    private $redis;
    private ?string $redisComponent;

    /**
     * @param array{redis?: mixed, redisComponent?: string} $config
     */
    public function __construct(array $config = [])
    {
        $this->redis = $config['redis'] ?? null;
        $this->redisComponent = $config['redisComponent'] ?? 'redis';
    }

    /**
     * @return array{allowed: bool, remaining: int, reset_at: int, retry_after: int}
     */
    public function consume(string $key, int $limit, int $windowSeconds): array
    {
        if ($limit <= 0 || $windowSeconds <= 0) {
            throw new RuntimeException('Invalid atomic rate-limit strategy.');
        }

        $response = $this->command('EVAL', [
            self::CONSUME_LUA,
            1,
            $key,
            $windowSeconds * 1000,
            $limit,
            bin2hex(random_bytes(16)),
        ]);

        if (!is_array($response) || count($response) < 4) {
            throw new RuntimeException('Invalid Redis rate-limit response.');
        }

        $allowed = $this->parseNonNegativeInteger($response[0] ?? null);
        $remaining = $this->parseNonNegativeInteger($response[1] ?? null);
        $resetAt = $this->parseNonNegativeInteger($response[2] ?? null);
        $retryAfter = $this->parseNonNegativeInteger($response[3] ?? null);
        if (
            $allowed === null
            || !in_array($allowed, [0, 1], true)
            || $remaining === null
            || $remaining > $limit
            || ($allowed === 1 && $remaining >= $limit)
            || ($allowed === 0 && $remaining !== 0)
            || $resetAt === null
            || $resetAt < 1
            || $retryAfter === null
            || $retryAfter < 1
            || $retryAfter > $windowSeconds
        ) {
            throw new RuntimeException('Invalid Redis rate-limit response.');
        }

        return [
            'allowed' => $allowed === 1,
            'remaining' => $remaining,
            'reset_at' => $resetAt,
            'retry_after' => $retryAfter,
        ];
    }

    /** @param mixed $value */
    private function parseNonNegativeInteger($value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || preg_match('/^(?:0|[1-9]\\d*)$/D', $value) !== 1) {
            return null;
        }

        $max = (string)PHP_INT_MAX;
        if (strlen($value) > strlen($max) || (strlen($value) === strlen($max) && strcmp($value, $max) > 0)) {
            return null;
        }

        return (int)$value;
    }

    /**
     * Compatibility operations for the generic RateLimiter interface. They
     * are not used by the login-code path, which must call consume().
     */
    public function add(string $key, float $timestamp): void
    {
        $timestampMs = (int)floor($timestamp * 1000);
        $this->command('ZADD', [$key, $timestampMs, $timestampMs . ':' . bin2hex(random_bytes(8))]);
    }

    public function count(string $key): int
    {
        return (int)$this->command('ZCARD', [$key]);
    }

    public function purgeExpired(string $key, float $threshold): void
    {
        $this->command('ZREMRANGEBYSCORE', [$key, '-inf', (int)floor($threshold * 1000)]);
    }

    public function getOldest(string $key): ?float
    {
        $result = $this->command('ZRANGE', [$key, 0, 0, 'WITHSCORES']);
        if (!is_array($result) || !isset($result[1])) {
            return null;
        }

        return ((float)$result[1]) / 1000;
    }

    public function clear(string $key): void
    {
        $this->command('DEL', [$key]);
    }

    /** @return mixed */
    private function redis()
    {
        if ($this->redis === null) {
            if ($this->redisComponent === null) {
                throw new RuntimeException('Redis rate-limit storage is not configured.');
            }
            $this->redis = Yii::$app->get($this->redisComponent);
        }

        return $this->redis;
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    private function command(string $command, array $arguments)
    {
        return $this->redis()->executeCommand($command, $arguments);
    }
}

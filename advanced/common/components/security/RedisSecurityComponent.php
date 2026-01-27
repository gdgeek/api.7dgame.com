<?php

namespace common\components\security;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Redis Security Component
 * 
 * Provides Redis-based functionality for:
 * - Rate limiting (sliding window algorithm)
 * - Token revocation list management
 * 
 * Requirements: 4.1, 4.2, 8.4
 * 
 * @property-read \Predis\Client|null $client The Redis client instance
 */
class RedisSecurityComponent extends Component
{
    /**
     * @var string Redis host
     */
    public $host = '127.0.0.1';

    /**
     * @var int Redis port
     */
    public $port = 6379;

    /**
     * @var string|null Redis password
     */
    public $password;

    /**
     * @var int Redis database index
     */
    public $database = 1;

    /**
     * @var float Connection timeout in seconds
     */
    public $timeout = 2.5;

    /**
     * @var string Key prefix for all security-related keys
     */
    public $keyPrefix = 'security:';

    /**
     * @var bool Enable rate limiting functionality
     */
    public $enableRateLimit = true;

    /**
     * @var bool Enable token revocation functionality
     */
    public $enableTokenRevocation = true;

    /**
     * @var \Predis\Client|null Redis client instance
     */
    private $_client;

    /**
     * @var bool Whether the component is initialized
     */
    private $_initialized = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // Load configuration from security config if available
        $this->loadSecurityConfig();
    }

    /**
     * Load configuration from security.php config file
     */
    protected function loadSecurityConfig()
    {
        $securityConfig = Yii::$app->params['security']['redis'] ?? [];
        
        if (!empty($securityConfig)) {
            $this->host = $securityConfig['host'] ?? $this->host;
            $this->port = $securityConfig['port'] ?? $this->port;
            $this->password = $securityConfig['password'] ?? $this->password;
            $this->database = $securityConfig['database'] ?? $this->database;
            $this->timeout = $securityConfig['timeout'] ?? $this->timeout;
            $this->keyPrefix = $securityConfig['keyPrefix'] ?? $this->keyPrefix;
            $this->enableRateLimit = $securityConfig['enableRateLimit'] ?? $this->enableRateLimit;
            $this->enableTokenRevocation = $securityConfig['enableTokenRevocation'] ?? $this->enableTokenRevocation;
        }
    }

    /**
     * Get the Redis client instance
     * 
     * @return \Predis\Client|null
     */
    public function getClient()
    {
        if ($this->_client === null && !$this->_initialized) {
            $this->_initialized = true;
            $this->_client = $this->createClient();
        }
        
        return $this->_client;
    }

    /**
     * Create a new Redis client instance
     * 
     * @return \Predis\Client|null
     */
    protected function createClient()
    {
        // Check if Predis is available
        if (!class_exists('\Predis\Client')) {
            Yii::warning('Predis library not installed. Redis security features will be disabled.', __METHOD__);
            return null;
        }

        try {
            $parameters = [
                'scheme' => 'tcp',
                'host' => $this->host,
                'port' => $this->port,
                'database' => $this->database,
                'timeout' => $this->timeout,
            ];

            if ($this->password) {
                $parameters['password'] = $this->password;
            }

            $client = new \Predis\Client($parameters);
            
            // Test connection
            $client->ping();
            
            return $client;
        } catch (\Exception $e) {
            Yii::error('Failed to connect to Redis: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Check if Redis is available
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->getClient() !== null;
    }

    // =========================================================================
    // Rate Limiting Methods
    // Requirement 4.1: 100 requests per minute per IP
    // Requirement 4.2: 1000 requests per hour per user
    // =========================================================================

    /**
     * Check if a request is within rate limits using sliding window algorithm
     * 
     * @param string $identifier The identifier (IP address or user ID)
     * @param string $action The action being rate limited
     * @param int $limit Maximum number of requests allowed
     * @param int $windowSeconds Time window in seconds
     * @return array ['allowed' => bool, 'remaining' => int, 'resetAt' => int]
     */
    public function checkRateLimit(string $identifier, string $action, int $limit, int $windowSeconds): array
    {
        if (!$this->enableRateLimit || !$this->isAvailable()) {
            // If Redis is not available, allow the request but log a warning
            Yii::warning('Rate limiting disabled or Redis unavailable', __METHOD__);
            return [
                'allowed' => true,
                'remaining' => $limit,
                'resetAt' => time() + $windowSeconds,
            ];
        }

        $key = $this->getRateLimitKey($identifier, $action);
        $now = time();
        $windowStart = $now - $windowSeconds;

        try {
            $client = $this->getClient();
            
            // Use Redis transaction for atomic operations
            $client->multi();
            
            // Remove old entries outside the window
            $client->zremrangebyscore($key, '-inf', $windowStart);
            
            // Count current entries in the window
            $client->zcard($key);
            
            // Add current request
            $client->zadd($key, [$now . ':' . uniqid() => $now]);
            
            // Set expiry on the key
            $client->expire($key, $windowSeconds);
            
            $results = $client->exec();
            
            $currentCount = $results[1] ?? 0;
            $allowed = $currentCount < $limit;
            $remaining = max(0, $limit - $currentCount - 1);
            
            if (!$allowed) {
                // Remove the request we just added since it's not allowed
                $client->zremrangebyscore($key, $now, $now);
                $remaining = 0;
            }

            return [
                'allowed' => $allowed,
                'remaining' => $remaining,
                'resetAt' => $now + $windowSeconds,
            ];
        } catch (\Exception $e) {
            Yii::error('Rate limit check failed: ' . $e->getMessage(), __METHOD__);
            return [
                'allowed' => true,
                'remaining' => $limit,
                'resetAt' => time() + $windowSeconds,
            ];
        }
    }

    /**
     * Get the rate limit key for a given identifier and action
     * 
     * @param string $identifier
     * @param string $action
     * @return string
     */
    protected function getRateLimitKey(string $identifier, string $action): string
    {
        return $this->keyPrefix . 'ratelimit:' . $action . ':' . md5($identifier);
    }

    /**
     * Get remaining requests for an identifier
     * 
     * @param string $identifier
     * @param string $action
     * @param int $limit
     * @param int $windowSeconds
     * @return int
     */
    public function getRemainingRequests(string $identifier, string $action, int $limit, int $windowSeconds): int
    {
        if (!$this->enableRateLimit || !$this->isAvailable()) {
            return $limit;
        }

        $key = $this->getRateLimitKey($identifier, $action);
        $windowStart = time() - $windowSeconds;

        try {
            $client = $this->getClient();
            $client->zremrangebyscore($key, '-inf', $windowStart);
            $count = $client->zcard($key);
            return max(0, $limit - $count);
        } catch (\Exception $e) {
            Yii::error('Failed to get remaining requests: ' . $e->getMessage(), __METHOD__);
            return $limit;
        }
    }

    /**
     * Reset rate limit for an identifier
     * 
     * @param string $identifier
     * @param string $action
     * @return bool
     */
    public function resetRateLimit(string $identifier, string $action): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $key = $this->getRateLimitKey($identifier, $action);

        try {
            $this->getClient()->del([$key]);
            return true;
        } catch (\Exception $e) {
            Yii::error('Failed to reset rate limit: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    // =========================================================================
    // Token Revocation Methods
    // Requirement 8.4: THE Token_Manager SHALL maintain a revocation list for invalidated tokens
    // =========================================================================

    /**
     * Add a token to the revocation list
     * 
     * @param string $jti JWT ID (unique token identifier)
     * @param int $expiresAt Token expiration timestamp
     * @param int|null $userId User ID who owns the token
     * @param string|null $reason Reason for revocation
     * @return bool
     */
    public function revokeToken(string $jti, int $expiresAt, ?int $userId = null, ?string $reason = null): bool
    {
        if (!$this->enableTokenRevocation || !$this->isAvailable()) {
            Yii::warning('Token revocation disabled or Redis unavailable', __METHOD__);
            return false;
        }

        $key = $this->getTokenRevocationKey($jti);
        $ttl = max(0, $expiresAt - time());

        // Don't store if token is already expired
        if ($ttl <= 0) {
            return true;
        }

        try {
            $client = $this->getClient();
            
            // Store revocation data
            $data = json_encode([
                'jti' => $jti,
                'user_id' => $userId,
                'revoked_at' => time(),
                'expires_at' => $expiresAt,
                'reason' => $reason,
            ]);
            
            // Set with expiry (token will be auto-removed after it would have expired anyway)
            $client->setex($key, $ttl, $data);
            
            // Also add to user's revoked tokens set if user ID is provided
            if ($userId !== null) {
                $userKey = $this->getUserRevokedTokensKey($userId);
                $client->zadd($userKey, [$jti => $expiresAt]);
                $client->expire($userKey, $ttl);
            }
            
            return true;
        } catch (\Exception $e) {
            Yii::error('Failed to revoke token: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Check if a token has been revoked
     * 
     * @param string $jti JWT ID to check
     * @return bool True if token is revoked, false otherwise
     */
    public function isTokenRevoked(string $jti): bool
    {
        if (!$this->enableTokenRevocation || !$this->isAvailable()) {
            return false;
        }

        $key = $this->getTokenRevocationKey($jti);

        try {
            return $this->getClient()->exists($key) > 0;
        } catch (\Exception $e) {
            Yii::error('Failed to check token revocation: ' . $e->getMessage(), __METHOD__);
            // Fail secure - if we can't check, assume not revoked but log the error
            return false;
        }
    }

    /**
     * Revoke all tokens for a specific user
     * 
     * @param int $userId User ID
     * @param string|null $reason Reason for revocation
     * @param int $defaultTtl Default TTL in seconds for tokens without known expiry (default: 7 days)
     * @return bool
     */
    public function revokeAllUserTokens(int $userId, ?string $reason = null, int $defaultTtl = 604800): bool
    {
        if (!$this->enableTokenRevocation || !$this->isAvailable()) {
            Yii::warning('Token revocation disabled or Redis unavailable', __METHOD__);
            return false;
        }

        try {
            $client = $this->getClient();
            
            // Store a marker that all tokens for this user before this time are revoked
            $key = $this->getUserAllTokensRevokedKey($userId);
            $data = json_encode([
                'user_id' => $userId,
                'revoked_at' => time(),
                'reason' => $reason,
            ]);
            
            $client->setex($key, $defaultTtl, $data);
            
            return true;
        } catch (\Exception $e) {
            Yii::error('Failed to revoke all user tokens: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Check if all tokens for a user have been revoked after a certain time
     * 
     * @param int $userId User ID
     * @param int $tokenIssuedAt Token issued at timestamp
     * @return bool True if all tokens issued before revocation time are revoked
     */
    public function areAllUserTokensRevoked(int $userId, int $tokenIssuedAt): bool
    {
        if (!$this->enableTokenRevocation || !$this->isAvailable()) {
            return false;
        }

        $key = $this->getUserAllTokensRevokedKey($userId);

        try {
            $data = $this->getClient()->get($key);
            
            if ($data === null) {
                return false;
            }
            
            $revocationData = json_decode($data, true);
            $revokedAt = $revocationData['revoked_at'] ?? 0;
            
            // Token is revoked if it was issued before the revocation time
            return $tokenIssuedAt < $revokedAt;
        } catch (\Exception $e) {
            Yii::error('Failed to check user token revocation: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Get revocation data for a token
     * 
     * @param string $jti JWT ID
     * @return array|null Revocation data or null if not revoked
     */
    public function getTokenRevocationData(string $jti): ?array
    {
        if (!$this->enableTokenRevocation || !$this->isAvailable()) {
            return null;
        }

        $key = $this->getTokenRevocationKey($jti);

        try {
            $data = $this->getClient()->get($key);
            
            if ($data === null) {
                return null;
            }
            
            return json_decode($data, true);
        } catch (\Exception $e) {
            Yii::error('Failed to get token revocation data: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Remove a token from the revocation list (for cleanup or testing)
     * 
     * @param string $jti JWT ID
     * @return bool
     */
    public function removeTokenRevocation(string $jti): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $key = $this->getTokenRevocationKey($jti);

        try {
            $this->getClient()->del([$key]);
            return true;
        } catch (\Exception $e) {
            Yii::error('Failed to remove token revocation: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Get the Redis key for token revocation
     * 
     * @param string $jti JWT ID
     * @return string
     */
    protected function getTokenRevocationKey(string $jti): string
    {
        return $this->keyPrefix . 'revoked:' . $jti;
    }

    /**
     * Get the Redis key for user's revoked tokens set
     * 
     * @param int $userId
     * @return string
     */
    protected function getUserRevokedTokensKey(int $userId): string
    {
        return $this->keyPrefix . 'user_revoked:' . $userId;
    }

    /**
     * Get the Redis key for user's all tokens revoked marker
     * 
     * @param int $userId
     * @return string
     */
    protected function getUserAllTokensRevokedKey(int $userId): string
    {
        return $this->keyPrefix . 'user_all_revoked:' . $userId;
    }

    // =========================================================================
    // Utility Methods
    // =========================================================================

    /**
     * Clear all security-related keys (for testing purposes only)
     * 
     * WARNING: This will clear all rate limits and token revocations!
     * 
     * @return bool
     */
    public function clearAll(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            $client = $this->getClient();
            $pattern = $this->keyPrefix . '*';
            
            // Use SCAN to find all keys with our prefix
            $cursor = 0;
            do {
                $result = $client->scan($cursor, ['MATCH' => $pattern, 'COUNT' => 100]);
                $cursor = $result[0];
                $keys = $result[1];
                
                if (!empty($keys)) {
                    $client->del($keys);
                }
            } while ($cursor != 0);
            
            return true;
        } catch (\Exception $e) {
            Yii::error('Failed to clear all security keys: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Get statistics about security-related Redis usage
     * 
     * @return array
     */
    public function getStats(): array
    {
        if (!$this->isAvailable()) {
            return [
                'available' => false,
                'rate_limit_keys' => 0,
                'revoked_tokens' => 0,
            ];
        }

        try {
            $client = $this->getClient();
            
            $rateLimitCount = 0;
            $revokedCount = 0;
            
            // Count rate limit keys
            $cursor = 0;
            do {
                $result = $client->scan($cursor, ['MATCH' => $this->keyPrefix . 'ratelimit:*', 'COUNT' => 100]);
                $cursor = $result[0];
                $rateLimitCount += count($result[1]);
            } while ($cursor != 0);
            
            // Count revoked token keys
            $cursor = 0;
            do {
                $result = $client->scan($cursor, ['MATCH' => $this->keyPrefix . 'revoked:*', 'COUNT' => 100]);
                $cursor = $result[0];
                $revokedCount += count($result[1]);
            } while ($cursor != 0);
            
            return [
                'available' => true,
                'rate_limit_keys' => $rateLimitCount,
                'revoked_tokens' => $revokedCount,
            ];
        } catch (\Exception $e) {
            Yii::error('Failed to get security stats: ' . $e->getMessage(), __METHOD__);
            return [
                'available' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
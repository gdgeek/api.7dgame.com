<?php

namespace api\modules\v1\services;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Immutable configuration for the short-lived QR login-code protocol.
 *
 * The defaults deliberately preserve the existing MySQL-only behaviour. Redis
 * is not touched until an operator explicitly selects a Redis read or write
 * mode in the develop environment.
 */
final class LoginCodeSettings
{
    public const READ_DATABASE = 'database';
    public const READ_REDIS_FIRST = 'redis-first';
    public const READ_REDIS = 'redis';

    public const WRITE_DATABASE = 'database';
    public const WRITE_DUAL = 'dual';
    public const WRITE_REDIS = 'redis';

    public const ACTIVE_WINDOW_SECONDS = 60;
    public const RECORD_RETENTION_SECONDS = 300;
    public const ISSUE_WINDOW_SECONDS = 60;
    public const PROTOCOL_VERSION = 'login-code-v1';
    /**
     * user_linked.created_at is an existing Asia/Shanghai wall-clock DATETIME
     * consumed by the legacy PHP paths. Dual writes preserve that representation
     * while redis-first converts it explicitly, never via a DB session zone.
     */
    public const LEGACY_DB_TIME_ZONE = '+08:00';

    private string $readMode;
    private string $writeMode;
    private string $prefix;
    private int $issueLimit;
    private bool $legacyDbAvailable;
    private ?string $expectedProtocolFingerprint;
    private string $protocolFingerprint;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $hasExplicitEmptyExpectedProtocolFingerprint = array_key_exists('protocolFingerprint', $config)
            && $config['protocolFingerprint'] === '';
        $this->readMode = strtolower(trim((string)($config['readMode'] ?? self::READ_DATABASE)));
        $this->writeMode = strtolower(trim((string)($config['writeMode'] ?? self::WRITE_DATABASE)));
        $this->prefix = rtrim(trim((string)($config['prefix'] ?? 'auth:login-code:v1')), ':');
        $this->issueLimit = $this->integerValue($config['issueLimit'] ?? 5, 'LOGIN_CODE_ISSUE_LIMIT');
        $this->expectedProtocolFingerprint = $this->optionalFingerprint(
            $config['protocolFingerprint'] ?? null
        );
        $this->legacyDbAvailable = $this->booleanValue(
            $config['legacyDbAvailable'] ?? true,
            'LOGIN_CODE_LEGACY_DB_AVAILABLE'
        );

        $activeWindow = $this->integerValue(
            $config['activeWindowSeconds'] ?? self::ACTIVE_WINDOW_SECONDS,
            'LOGIN_CODE_ACTIVE_WINDOW_SECONDS'
        );
        $recordRetention = $this->integerValue(
            $config['recordRetentionSeconds'] ?? self::RECORD_RETENTION_SECONDS,
            'LOGIN_CODE_RECORD_TTL_SECONDS'
        );
        $issueWindow = $this->integerValue(
            $config['issueWindowSeconds'] ?? self::ISSUE_WINDOW_SECONDS,
            'LOGIN_CODE_ISSUE_WINDOW_SECONDS'
        );

        if (!in_array($this->readMode, [self::READ_DATABASE, self::READ_REDIS_FIRST, self::READ_REDIS], true)) {
            throw new InvalidConfigException('LOGIN_CODE_READ_MODE is invalid.');
        }

        if (!in_array($this->writeMode, [self::WRITE_DATABASE, self::WRITE_DUAL, self::WRITE_REDIS], true)) {
            throw new InvalidConfigException('LOGIN_CODE_WRITE_MODE is invalid.');
        }

        if (preg_match('/^[a-z][a-z0-9:_-]{0,127}$/D', $this->prefix) !== 1) {
            throw new InvalidConfigException('LOGIN_CODE_REDIS_PREFIX must use the v1 namespace form.');
        }

        if ($activeWindow !== self::ACTIVE_WINDOW_SECONDS || $recordRetention !== self::RECORD_RETENTION_SECONDS || $issueWindow !== self::ISSUE_WINDOW_SECONDS) {
            throw new InvalidConfigException('The v1 login-code time windows are protocol constants (60/300/60 seconds).');
        }

        if ($this->issueLimit < 2 || $this->issueLimit > 20) {
            throw new InvalidConfigException('LOGIN_CODE_ISSUE_LIMIT must be an integer from 2 through 20.');
        }

        $supportedModePairs = [
            self::READ_DATABASE . '/' . self::WRITE_DATABASE,
            self::READ_DATABASE . '/' . self::WRITE_DUAL,
            self::READ_REDIS_FIRST . '/' . self::WRITE_DUAL,
            self::READ_REDIS_FIRST . '/' . self::WRITE_REDIS,
            self::READ_REDIS . '/' . self::WRITE_REDIS,
        ];
        if (!in_array($this->readMode . '/' . $this->writeMode, $supportedModePairs, true)) {
            throw new InvalidConfigException('The selected login-code read/write mode pair is not supported.');
        }

        if (!$this->legacyDbAvailable && ($this->readMode !== self::READ_REDIS || $this->writeMode !== self::WRITE_REDIS)) {
            throw new InvalidConfigException('LOGIN_CODE_LEGACY_DB_AVAILABLE=false only permits redis/redis mode.');
        }

        $this->protocolFingerprint = self::protocolFingerprintFor($this->prefix);
        if (
            $this->usesRedis()
            && $hasExplicitEmptyExpectedProtocolFingerprint
        ) {
            throw new InvalidConfigException('LOGIN_CODE_PROTOCOL_FINGERPRINT must not be empty when Redis mode is enabled.');
        }

        if (
            $this->usesRedis()
            && $this->expectedProtocolFingerprint !== null
            && !hash_equals($this->expectedProtocolFingerprint, $this->protocolFingerprint)
        ) {
            throw new InvalidConfigException('LOGIN_CODE_PROTOCOL_FINGERPRINT does not match the v1 protocol settings.');
        }
    }

    public static function fromApplication(): self
    {
        $configured = [];
        if (Yii::$app !== null && isset(Yii::$app->params['loginCode']) && is_array(Yii::$app->params['loginCode'])) {
            $configured = Yii::$app->params['loginCode'];
        }

        return new self($configured);
    }

    public function readMode(): string
    {
        return $this->readMode;
    }

    public function writeMode(): string
    {
        return $this->writeMode;
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    public function protocolFingerprint(): string
    {
        return $this->protocolFingerprint;
    }

    public static function defaultProtocolFingerprint(): string
    {
        return self::protocolFingerprintFor('auth:login-code:v1');
    }

    public static function protocolFingerprintFor(string $prefix): string
    {
        return hash('sha256', implode("\n", [
            self::PROTOCOL_VERSION,
            rtrim(trim($prefix), ':'),
            (string)self::ACTIVE_WINDOW_SECONDS,
            (string)self::RECORD_RETENTION_SECONDS,
        ]));
    }

    public function issueLimit(): int
    {
        return $this->issueLimit;
    }

    public function legacyDbAvailable(): bool
    {
        return $this->legacyDbAvailable;
    }

    public function readsRedis(): bool
    {
        return $this->readMode !== self::READ_DATABASE;
    }

    public function writesRedis(): bool
    {
        return $this->writeMode !== self::WRITE_DATABASE;
    }

    public function usesRedis(): bool
    {
        return $this->readsRedis() || $this->writesRedis();
    }

    public function isRedisFirst(): bool
    {
        return $this->readMode === self::READ_REDIS_FIRST;
    }

    public function isRedisOnlyRead(): bool
    {
        return $this->readMode === self::READ_REDIS;
    }

    public function isDualWrite(): bool
    {
        return $this->writeMode === self::WRITE_DUAL;
    }

    /** @param mixed $value */
    private function integerValue($value, string $name): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', trim($value)) === 1) {
            return (int)trim($value);
        }

        throw new InvalidConfigException($name . ' must be an integer.');
    }

    /** @param mixed $value */
    private function booleanValue($value, string $name): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) && ($value === 0 || $value === 1)) {
            return (bool)$value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        throw new InvalidConfigException($name . ' must be a boolean.');
    }

    /** @param mixed $value */
    private function optionalFingerprint($value): ?string
    {
        if ($value === null || $value === false || $value === '') {
            return null;
        }

        if (!is_string($value) || preg_match('/^[a-f0-9]{64}$/D', $value) !== 1) {
            throw new InvalidConfigException('LOGIN_CODE_PROTOCOL_FINGERPRINT must be a lowercase SHA-256 hex value.');
        }

        return $value;
    }
}

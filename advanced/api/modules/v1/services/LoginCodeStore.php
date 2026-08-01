<?php

namespace api\modules\v1\services;

use api\modules\v1\RefreshToken;
use api\modules\v1\models\UserLinked;
use api\modules\v1\models\User;
use common\components\security\ServiceUnavailableHttpException;
use JsonException;
use Throwable;
use Yii;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Authoritative storage boundary for QR device-login codes.
 *
 * A Redis Code_Record is immutable and is addressed only by the SHA-256 digest
 * of the raw code. The class deliberately has no user -> code index: multiple
 * login codes may coexist and a caller can only resolve the code it holds.
 */
final class LoginCodeStore
{
    private const RECORD_VERSION = 1;
    private const PURPOSE = 'web-device-login';
    private const ISSUER = 'main-api';
    private const MAX_RECORD_BYTES = 2048;
    private const MAX_ISSUE_ATTEMPTS = 5;
    private const MAX_CODE_GENERATION_ATTEMPTS = 5;
    private const ACTIVE_PTTL_MILLISECONDS = 240000;
    private const CONTEXT_MAX_STRING_BYTES = 128;
    private const CONTEXT_ALLOWED_FIELDS = ['device', 'source', 'client_version'];

    /** @var mixed|null */
    private $redis;
    private LoginCodeSettings $settings;
    private ?LoginCodeReadiness $readiness;
    private string $telemetrySource = 'main-api-refresh';
    /** @var callable|null */
    private $codeGenerator;

    /**
     * @param mixed|null $redis yii\redis\Connection in production; an adapter is accepted in unit tests.
     */
    public function __construct(
        $redis = null,
        ?LoginCodeSettings $settings = null,
        ?LoginCodeReadiness $readiness = null,
        ?callable $codeGenerator = null
    )
    {
        $this->redis = $redis;
        $this->settings = $settings ?? LoginCodeSettings::fromApplication();
        $this->readiness = $readiness;
        $this->codeGenerator = $codeGenerator;
    }

    public function settings(): LoginCodeSettings
    {
        return $this->settings;
    }

    /**
     * @param array<string, mixed> $context
     * @return array{key: string, expires_at: int, expires_in: int}
     */
    public function issue(int $userId, array $context = []): array
    {
        $this->telemetrySource = 'main-api-issue';
        if ($userId <= 0) {
            throw new ServerErrorHttpException('Unable to issue login code.');
        }

        if ($this->settings->writeMode() === LoginCodeSettings::WRITE_DATABASE) {
            return $this->issueLegacy($userId);
        }

        $this->validateContext($context);

        $this->assertRedisReady();
        return $this->issueRedis($userId, $context);
    }

    /**
     * Resolve a code for authentication.
     *
     * @return array{outcome: string, user_id?: int, expires_at?: int, expires_in?: int}
     */
    public function resolve(string $rawCode): array
    {
        $this->telemetrySource = 'main-api-refresh';
        $rawCode = self::normalizeInput($rawCode);
        if ($rawCode === '') {
            return ['outcome' => 'miss'];
        }

        if (!$this->settings->readsRedis()) {
            return $this->resolveLegacy($rawCode, null, false);
        }

        $this->assertRedisReady();
        $result = $this->resolveRedis($rawCode);
        if ($result['outcome'] !== 'miss' || !$this->settings->isRedisFirst()) {
            return $result;
        }

        /** @var int $nowMilliseconds */
        $nowMilliseconds = $result['now_ms'];
        return $this->resolveLegacy($rawCode, intdiv($nowMilliseconds, 1000), true);
    }

    /**
     * Resolve the status of a code while hiding ownership from other users.
     *
     * @return array{active: bool, reason: string, expires_at?: int, expires_in?: int}
     */
    public function status(int $userId, string $rawCode): array
    {
        $this->telemetrySource = 'main-api-status';
        $rawCode = self::normalizeInput($rawCode);
        if ($rawCode === '') {
            return ['active' => false, 'reason' => 'not_found'];
        }

        if (!$this->settings->readsRedis()) {
            return $this->legacyStatus($userId, $rawCode, null, false);
        }

        $this->assertRedisReady();
        $result = $this->resolveRedis($rawCode);
        if ($result['outcome'] === 'miss' && $this->settings->isRedisFirst()) {
            return $this->legacyStatus($userId, $rawCode, intdiv((int)$result['now_ms'], 1000), true);
        }

        return $this->statusFromResolveResult($userId, $result);
    }

    /**
     * @param array{outcome: string, user_id?: int, expires_at?: int, expires_in?: int} $result
     * @return array{active: bool, reason: string, expires_at?: int, expires_in?: int}
     */
    private function statusFromResolveResult(int $userId, array $result): array
    {
        $outcome = $result['outcome'];

        if ($outcome === 'miss') {
            $this->telemetry('miss');
            return ['active' => false, 'reason' => 'not_found'];
        }

        $recordUserId = (int)($result['user_id'] ?? 0);
        if ($recordUserId !== $userId) {
            $this->telemetry('miss');
            return ['active' => false, 'reason' => 'not_found'];
        }

        if ($outcome === 'expired') {
            $this->telemetry('expired');
            return [
                'active' => false,
                'reason' => 'expired',
                'expires_at' => (int)$result['expires_at'],
                'expires_in' => 0,
            ];
        }

        $this->telemetry('active');
        return [
            'active' => true,
            'reason' => 'active',
            'expires_at' => (int)$result['expires_at'],
            'expires_in' => (int)$result['expires_in'],
        ];
    }

    /**
     * Exact cleanup used solely for a failed dual-write compensation path.
     */
    public function deleteExact(string $rawCode): void
    {
        $rawCode = self::normalizeInput($rawCode);
        if ($rawCode === '') {
            return;
        }

        $this->redisCommand('DEL', [$this->codeKey($rawCode)]);
    }

    public static function normalizeInput(string $value): string
    {
        $value = trim($value);

        if (preg_match('/(?:^|[?&])web_([^&#\s]+)/', $value, $matches) === 1) {
            return $matches[1];
        }

        if (str_starts_with($value, 'web_')) {
            return substr($value, 4);
        }

        return $value;
    }

    /**
     * @return array{key: string, expires_at: int, expires_in: int}
     */
    private function issueLegacy(int $userId): array
    {
        // The legacy contract is "latest code wins". Historical duplicate
        // rows exist, so every issue path must update the same canonical row.
        $linked = UserLinked::find()->where(['user_id' => $userId])->orderBy(['id' => SORT_DESC])->one();
        if (!$linked instanceof UserLinked) {
            $linked = new UserLinked();
            $linked->user_id = $userId;
        }

        $rawCode = $this->generateRawCode();
        $linked->key = RefreshToken::hashToken($rawCode);
        if (!$linked->validate()) {
            throw new BadRequestHttpException('validate error');
        }
        try {
            $saved = $this->withoutLoginCodeDbLogging(static function () use ($linked): bool {
                return $linked->save();
            });
        } catch (Throwable $exception) {
            Yii::error('Login-code legacy persistence failed.', 'login-code');
            throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
        }

        if (!$saved) {
            throw new BadRequestHttpException('save error');
        }

        $expiresAt = time() + LoginCodeSettings::ACTIVE_WINDOW_SECONDS;
        $this->telemetry('issued');
        return [
            'key' => $rawCode,
            'expires_at' => $expiresAt,
            'expires_in' => max(0, $expiresAt - time()),
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return array{key: string, expires_at: int, expires_in: int}
     */
    private function issueRedis(int $userId, array $context): array
    {
        for ($attempt = 0; $attempt < self::MAX_ISSUE_ATTEMPTS; ++$attempt) {
            $rawCode = $this->generateRawCode();
            if ($this->settings->isDualWrite()) {
                $issued = $this->issueDual($userId, $rawCode, $context);
                if ($issued === null) {
                    continue;
                }

                return $issued;
            }

            $issuedAt = intdiv($this->redisTimeMilliseconds(), 1000);
            $expiresAt = $issuedAt + LoginCodeSettings::ACTIVE_WINDOW_SECONDS;
            $payload = $this->encodeRecord($userId, $issuedAt, $expiresAt, $context);
            $result = $this->redisCommand('SET', [
                $this->codeKey($rawCode),
                $payload,
                'PX',
                LoginCodeSettings::RECORD_RETENTION_SECONDS * 1000,
                'NX',
            ]);

            if (!$this->setSucceeded($result)) {
                continue;
            }

            $this->telemetry('issued');
            return [
                'key' => $rawCode,
                'expires_at' => $expiresAt,
                'expires_in' => LoginCodeSettings::ACTIVE_WINDOW_SECONDS,
            ];
        }

        $this->telemetry('redis_write_failed');
        Yii::error('Login-code digest collision retry limit reached.', 'login-code');
        throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
    }

    /**
     * @param array<string, mixed> $context
     * @return array{key: string, expires_at: int, expires_in: int}|null null only for a SET NX collision
     */
    private function issueDual(int $userId, string $rawCode, array $context): ?array
    {
        $transaction = null;
        $redisWritten = false;
        try {
            $transaction = Yii::$app->db->beginTransaction();
            // Lock the parent user row, which exists even before this user has
            // a legacy user_linked row. That serializes dual writes per user.
            $this->lockLegacyUser($userId);

            $issuedAt = intdiv($this->redisTimeMilliseconds(), 1000);
            $expiresAt = $issuedAt + LoginCodeSettings::ACTIVE_WINDOW_SECONDS;
            $payload = $this->encodeRecord($userId, $issuedAt, $expiresAt, $context);
            $result = $this->redisCommand('SET', [
                $this->codeKey($rawCode),
                $payload,
                'PX',
                LoginCodeSettings::RECORD_RETENTION_SECONDS * 1000,
                'NX',
            ]);

            if (!$this->setSucceeded($result)) {
                $transaction->rollBack();
                return null;
            }

            $redisWritten = true;
            $this->persistLegacyLatestCode($userId, RefreshToken::hashToken($rawCode), $issuedAt);

            $transaction->commit();

            $this->telemetry('dual_write_success');
            $this->telemetry('issued');

            return [
                'key' => $rawCode,
                'expires_at' => $expiresAt,
                'expires_in' => LoginCodeSettings::ACTIVE_WINDOW_SECONDS,
            ];
        } catch (Throwable $exception) {
            if ($transaction !== null && $transaction->isActive) {
                $transaction->rollBack();
            }

            if ($redisWritten) {
                try {
                    $this->deleteExact($rawCode);
                } catch (Throwable $compensationException) {
                    $this->telemetry('compensation_failed');
                    Yii::error('Login-code dual-write compensation failed.', 'login-code');
                }
            }

            $this->telemetry($redisWritten ? 'db_write_failed' : 'redis_write_failed');
            Yii::error('Login-code dual-write database persistence failed.', 'login-code');
            throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
        }
    }

    private function lockLegacyUser(int $userId): void
    {
        $lockedUserId = Yii::$app->db->createCommand(
            'SELECT [[id]] FROM ' . User::tableName() . ' WHERE [[id]] = :userId FOR UPDATE',
            [':userId' => $userId]
        )->queryScalar();

        if ((int)$lockedUserId !== $userId) {
            throw new ServerErrorHttpException('Unable to issue login code.');
        }
    }

    /**
     * Persists the legacy shadow row inside the transaction owned by issueDual().
     */
    private function persistLegacyLatestCode(int $userId, string $digest, int $issuedAt): void
    {
        $linked = UserLinked::find()->where(['user_id' => $userId])->orderBy(['id' => SORT_DESC])->one();
        $createdAt = $this->legacyCreatedAtExpression($issuedAt);

        if ($linked instanceof UserLinked) {
            $this->withoutLoginCodeDbLogging(static function () use ($linked, $digest, $createdAt): void {
                Yii::$app->db->createCommand()
                    ->update(UserLinked::tableName(), ['key' => $digest, 'created_at' => $createdAt], ['id' => $linked->id])
                    ->execute();
            });
            return;
        }

        $this->withoutLoginCodeDbLogging(static function () use ($userId, $digest, $createdAt): void {
            Yii::$app->db->createCommand()
                ->insert(UserLinked::tableName(), ['user_id' => $userId, 'key' => $digest, 'created_at' => $createdAt])
                ->execute();
        });
    }

    /**
     * @return array{outcome: string, user_id?: int, expires_at?: int, expires_in?: int, now_ms?: int}
     */
    private function resolveRedis(string $rawCode): array
    {
        $key = $this->codeKey($rawCode);
        $payload = $this->redisCommand('GET', [$key]);
        $pttl = $this->parseRedisInteger($this->redisCommand('PTTL', [$key]));
        if ($pttl === null) {
            $this->redisProtocolFailure('Login-code Redis PTTL response is invalid.');
        }
        $nowMilliseconds = $this->redisTimeMilliseconds();

        if ($payload === null) {
            if ($pttl === -2) {
                $this->telemetry('miss');
                return ['outcome' => 'miss', 'now_ms' => $nowMilliseconds];
            }

            // A live TTL without a value is not a healthy miss. It may be a
            // corrupt record or an inconsistent Redis response, so fail closed
            // rather than falling back to the single-code legacy row.
            $this->malformedRecord();
        }

        if (!is_string($payload)) {
            $this->malformedRecord();
        }

        if ($pttl === -2) {
            // The record disappeared after GET. Treat this race as a miss;
            // this can only reject a code earlier, never authorize it.
            $this->telemetry('miss');
            return ['outcome' => 'miss', 'now_ms' => $nowMilliseconds];
        }

        if ($pttl === -1 || $pttl < -2 || $pttl > LoginCodeSettings::RECORD_RETENTION_SECONDS * 1000) {
            $this->malformedRecord();
        }

        $record = $this->decodeRecord((string)$payload);
        $expiresAt = (int)$record->expires_at;
        $userId = (int)$record->user_id;

        if ((int)$record->issued_at > intdiv($nowMilliseconds, 1000)) {
            $this->malformedRecord();
        }

        if ($nowMilliseconds >= $expiresAt * 1000 || $pttl <= self::ACTIVE_PTTL_MILLISECONDS) {
            $this->telemetry('expired');
            return [
                'outcome' => 'expired',
                'user_id' => $userId,
                'expires_at' => $expiresAt,
                'expires_in' => 0,
            ];
        }

        $timeRemaining = ($expiresAt * 1000) - $nowMilliseconds;
        $ttlRemaining = $pttl - self::ACTIVE_PTTL_MILLISECONDS;
        $expiresIn = (int)ceil(min($timeRemaining, $ttlRemaining) / 1000);

        $this->telemetry('redis_hit');
        return [
            'outcome' => 'hit',
            'user_id' => $userId,
            'expires_at' => $expiresAt,
            'expires_in' => max(0, $expiresIn),
        ];
    }

    /**
     * @return array{outcome: string, user_id?: int, expires_at?: int, expires_in?: int}
     */
    private function resolveLegacy(string $rawCode, ?int $nowSeconds, bool $retentionAware): array
    {
        if ($retentionAware) {
            // Fetch the matched key and its MySQL-normalized timestamp in one
            // statement. A second timestamp query would allow a concurrent
            // dual write to replace code A with B between reads and could
            // incorrectly authorize A using B's fresh created_at.
            $fallbackRecord = $this->findCurrentLegacyFallbackRecord($rawCode);
            if ($fallbackRecord === null) {
                $this->telemetry('miss');
                return ['outcome' => 'miss'];
            }

            $createdAt = $fallbackRecord['created_at_epoch'];
            $userId = $fallbackRecord['user_id'];
        } else {
            $legacyRecord = $this->findCurrentLegacyRecord($rawCode);
            if ($legacyRecord === null) {
                return ['outcome' => 'miss'];
            }

            $createdAt = $this->legacyTimestamp($legacyRecord['created_at']);
            $userId = $legacyRecord['user_id'];
        }

        $expiresAt = $createdAt + LoginCodeSettings::ACTIVE_WINDOW_SECONDS;

        if ($createdAt <= 0 || $userId <= 0) {
            if ($retentionAware) {
                $this->telemetry('miss');
            }
            return ['outcome' => 'miss'];
        }

        if ($retentionAware) {
            $nowSeconds ??= time();
            if ($nowSeconds < $createdAt) {
                // A legacy row dated after Redis TIME cannot safely represent
                // a code issued by this protocol. Reject rather than granting
                // a window longer than 60 seconds on clock/session anomalies.
                $this->telemetry('expired');
                return [
                    'outcome' => 'expired',
                    'user_id' => $userId,
                    'expires_at' => $expiresAt,
                    'expires_in' => 0,
                ];
            }
            if ($nowSeconds >= $createdAt + LoginCodeSettings::RECORD_RETENTION_SECONDS) {
                $this->telemetry('miss');
                return ['outcome' => 'miss'];
            }
            if ($nowSeconds >= $expiresAt) {
                $this->telemetry('expired');
                return [
                    'outcome' => 'expired',
                    'user_id' => $userId,
                    'expires_at' => $expiresAt,
                    'expires_in' => 0,
                ];
            }

            $this->telemetry('db_fallback_hit');
            return [
                'outcome' => 'hit',
                'user_id' => $userId,
                'expires_at' => $expiresAt,
                'expires_in' => max(0, min(LoginCodeSettings::ACTIVE_WINDOW_SECONDS, $expiresAt - $nowSeconds)),
            ];
        }

        if ($createdAt + LoginCodeSettings::ACTIVE_WINDOW_SECONDS <= time()) {
            return [
                'outcome' => 'expired',
                'user_id' => $userId,
                'expires_at' => $expiresAt,
                'expires_in' => 0,
            ];
        }

        return [
            'outcome' => 'hit',
            'user_id' => $userId,
            'expires_at' => $expiresAt,
            'expires_in' => max(0, $expiresAt - time()),
        ];
    }

    /**
     * Status fallback keeps the legacy indexed user_id lookup. Never query the
     * unindexed key column from a status request controlled by the caller.
     *
     * @return array{active: bool, reason: string, expires_at?: int, expires_in?: int}
     */
    private function legacyStatus(int $userId, string $rawCode, ?int $nowSeconds, bool $retentionAware): array
    {
        if ($retentionAware) {
            // Keep the latest-row check, code comparison and timestamp in a
            // single snapshot for the same reason as resolveLegacy().
            $fallbackRecord = $this->findCurrentLegacyStatusFallbackRecord($userId, $rawCode);
            if ($fallbackRecord === null) {
                $this->telemetry('miss');
                return ['active' => false, 'reason' => 'not_found'];
            }

            $createdAt = $fallbackRecord['created_at_epoch'];
        } else {
            $linked = $this->findLegacyRecordForUser($userId, $rawCode);
            if (!$linked instanceof UserLinked) {
                return ['active' => false, 'reason' => 'not_found'];
            }

            $createdAt = $this->legacyTimestamp((string)$linked->created_at);
        }

        if ($createdAt <= 0) {
            if ($retentionAware) {
                $this->telemetry('miss');
            }
            return ['active' => false, 'reason' => 'not_found'];
        }

        $expiresAt = $createdAt + LoginCodeSettings::ACTIVE_WINDOW_SECONDS;
        if ($retentionAware) {
            $nowSeconds ??= time();
            if ($nowSeconds < $createdAt) {
                $this->telemetry('expired');
                return [
                    'active' => false,
                    'reason' => 'expired',
                    'expires_at' => $expiresAt,
                    'expires_in' => 0,
                ];
            }
            if ($nowSeconds >= $createdAt + LoginCodeSettings::RECORD_RETENTION_SECONDS) {
                $this->telemetry('miss');
                return ['active' => false, 'reason' => 'not_found'];
            }
            if ($nowSeconds >= $expiresAt) {
                $this->telemetry('expired');
                return [
                    'active' => false,
                    'reason' => 'expired',
                    'expires_at' => $expiresAt,
                    'expires_in' => 0,
                ];
            }

            $this->telemetry('db_fallback_hit');
            $this->telemetry('active');
            return [
                'active' => true,
                'reason' => 'active',
                'expires_at' => $expiresAt,
                'expires_in' => max(0, min(LoginCodeSettings::ACTIVE_WINDOW_SECONDS, $expiresAt - $nowSeconds)),
            ];
        }

        if ($createdAt + LoginCodeSettings::ACTIVE_WINDOW_SECONDS <= time()) {
            $this->telemetry('expired');
            return [
                'active' => false,
                'reason' => 'expired',
                'expires_at' => $expiresAt,
                'expires_in' => 0,
            ];
        }

        $this->telemetry('active');
        return [
            'active' => true,
            'reason' => 'active',
            'expires_at' => $expiresAt,
            'expires_in' => max(0, $expiresAt - time()),
        ];
    }

    /**
     * Database mode must keep the same "latest code wins" rule as status and
     * dual mode, even when historical duplicate user_linked rows exist.
     *
     * @return array{user_id: int, created_at: string}|null
     */
    private function findCurrentLegacyRecord(string $rawCode): ?array
    {
        $lookupKeys = $this->legacyLookupKeys($rawCode);
        $params = [];
        $placeholders = [];
        foreach ($lookupKeys as $index => $key) {
            $placeholder = ':loginCodeKey' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $key;
        }

        $table = UserLinked::tableName();
        $sql = 'SELECT [[linked]].[[user_id]], [[linked]].[[created_at]]'
            . ' FROM ' . $table . ' AS [[linked]]'
            . ' WHERE [[linked]].[[key]] IN (' . implode(', ', $placeholders) . ')'
            . ' AND NOT EXISTS ('
            . 'SELECT 1 FROM ' . $table . ' AS [[newer]]'
            . ' WHERE [[newer]].[[user_id]] = [[linked]].[[user_id]]'
            . ' AND [[newer]].[[id]] > [[linked]].[[id]]'
            . ')'
            . ' ORDER BY [[linked]].[[id]] DESC LIMIT 1';

        try {
            $row = $this->withoutLoginCodeDbLogging(static function () use ($sql, $params) {
                return Yii::$app->db->createCommand($sql, $params)->queryOne();
            });
        } catch (Throwable $exception) {
            Yii::error('Login-code legacy lookup failed.', 'login-code');
            throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
        }

        if (!is_array($row)) {
            return null;
        }

        $userId = is_numeric($row['user_id'] ?? null) ? (int)$row['user_id'] : 0;
        $createdAt = (string)($row['created_at'] ?? '');
        if ($userId <= 0 || $createdAt === '') {
            return null;
        }

        return ['user_id' => $userId, 'created_at' => $createdAt];
    }

    private function findLegacyRecordForUser(int $userId, string $rawCode): ?UserLinked
    {
        $linked = UserLinked::find()->where(['user_id' => $userId])->orderBy(['id' => SORT_DESC])->one();
        if (!$linked instanceof UserLinked) {
            return null;
        }

        if (!$this->storedLegacyKeyMatches($rawCode, (string)$linked->key)) {
            return null;
        }

        return $linked;
    }

    /**
     * redis-first fallback may only use the current legacy row for a user.
     * The NOT EXISTS predicate rejects historical duplicate rows left by old
     * data, so an earlier code cannot become valid again after a newer code
     * replaced it. Key, user and MySQL timestamp come from one DB snapshot.
     *
     * @return array{user_id: int, created_at_epoch: int}|null
     */
    private function findCurrentLegacyFallbackRecord(string $rawCode): ?array
    {
        $lookupKeys = $this->legacyLookupKeys($rawCode);
        $params = [];
        $placeholders = [];
        foreach ($lookupKeys as $index => $key) {
            $placeholder = ':loginCodeKey' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $key;
        }

        $table = UserLinked::tableName();
        $sql = 'SELECT [[linked]].[[user_id]], '
            . $this->legacyCreatedAtEpochSql('[[linked]].[[created_at]]') . ' AS [[created_at_epoch]]'
            . ' FROM ' . $table . ' AS [[linked]]'
            . ' WHERE [[linked]].[[key]] IN (' . implode(', ', $placeholders) . ')'
            . ' AND NOT EXISTS ('
            . 'SELECT 1 FROM ' . $table . ' AS [[newer]]'
            . ' WHERE [[newer]].[[user_id]] = [[linked]].[[user_id]]'
            . ' AND [[newer]].[[id]] > [[linked]].[[id]]'
            . ')'
            . ' ORDER BY [[linked]].[[id]] DESC LIMIT 1';

        return $this->queryLegacyFallbackRecord($sql, $params);
    }

    /**
     * Status keeps its indexed user_id lookup while taking the current key and
     * timestamp from the same query snapshot.
     *
     * @return array{user_id: int, created_at_epoch: int}|null
     */
    private function findCurrentLegacyStatusFallbackRecord(int $userId, string $rawCode): ?array
    {
        $table = UserLinked::tableName();
        $sql = 'SELECT [[linked]].[[user_id]], [[linked]].[[key]], '
            . $this->legacyCreatedAtEpochSql('[[linked]].[[created_at]]') . ' AS [[created_at_epoch]]'
            . ' FROM ' . $table . ' AS [[linked]]'
            . ' WHERE [[linked]].[[user_id]] = :loginCodeUserId'
            . ' ORDER BY [[linked]].[[id]] DESC LIMIT 1';

        try {
            $row = Yii::$app->db->createCommand($sql, [':loginCodeUserId' => $userId])->queryOne();
        } catch (Throwable $exception) {
            Yii::error('Login-code legacy fallback lookup failed.', 'login-code');
            throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
        }

        if (!is_array($row) || !$this->storedLegacyKeyMatches($rawCode, (string)($row['key'] ?? ''))) {
            return null;
        }

        return $this->normalizeLegacyFallbackRecord($row);
    }

    /**
     * @param array<string, mixed> $params
     * @return array{user_id: int, created_at_epoch: int}|null
     */
    private function queryLegacyFallbackRecord(string $sql, array $params): ?array
    {
        try {
            $row = $this->withoutLoginCodeDbLogging(static function () use ($sql, $params) {
                return Yii::$app->db->createCommand($sql, $params)->queryOne();
            });
        } catch (Throwable $exception) {
            Yii::error('Login-code legacy fallback lookup failed.', 'login-code');
            throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
        }

        return is_array($row) ? $this->normalizeLegacyFallbackRecord($row) : null;
    }

    /**
     * @param array<string, mixed> $row
     * @return array{user_id: int, created_at_epoch: int}|null
     */
    private function normalizeLegacyFallbackRecord(array $row): ?array
    {
        $userId = is_numeric($row['user_id'] ?? null) ? (int)$row['user_id'] : 0;
        $createdAt = is_numeric($row['created_at_epoch'] ?? null) ? (int)$row['created_at_epoch'] : 0;
        if ($userId <= 0 || $createdAt <= 0) {
            return null;
        }

        return ['user_id' => $userId, 'created_at_epoch' => $createdAt];
    }

    /**
     * Legacy user_linked.key stores SHA-256(rawCode), never the bearer code
     * itself. Accepting the supplied value as an alternate lookup key would
     * turn a leaked digest into a usable credential (and expose a Redis key
     * digest to redis-first DB fallback). Always hash the caller input once.
     *
     * @return list<string>
     */
    private function legacyLookupKeys(string $rawCode): array
    {
        return [RefreshToken::hashToken($rawCode)];
    }

    private function storedLegacyKeyMatches(string $rawCode, string $storedKey): bool
    {
        $digest = RefreshToken::hashToken($rawCode);
        return hash_equals($storedKey, $digest);
    }

    private function legacyCreatedAtEpochSql(string $column): string
    {
        return "TIMESTAMPDIFF(SECOND, '1970-01-01 00:00:00', CONVERT_TZ("
            . $column . ", '" . LoginCodeSettings::LEGACY_DB_TIME_ZONE . "', '+00:00'))";
    }

    private function legacyCreatedAtExpression(int $issuedAt): Expression
    {
        // created_at must remain an Asia/Shanghai wall-clock DATETIME so the
        // database/database legacy path keeps its existing strtotime() rule.
        // Unlike FROM_UNIXTIME(), this expression is independent of whichever
        // MySQL session timezone happens to serve main API or Yii3-A1.
        return new Expression(
            "CONVERT_TZ(DATE_ADD('1970-01-01 00:00:00', INTERVAL :loginCodeIssuedAt SECOND), '+00:00', '"
            . LoginCodeSettings::LEGACY_DB_TIME_ZONE . "')",
            [':loginCodeIssuedAt' => $issuedAt]
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function encodeRecord(int $userId, int $issuedAt, int $expiresAt, array $context): string
    {
        try {
            $payload = json_encode([
                'v' => self::RECORD_VERSION,
                'user_id' => $userId,
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
                'purpose' => self::PURPOSE,
                'issuer' => self::ISSUER,
                'context' => (object)$context,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $exception) {
            Yii::error('Login-code record serialization failed.', 'login-code');
            throw new ServerErrorHttpException('Unable to issue login code.');
        }

        if (strlen($payload) > self::MAX_RECORD_BYTES) {
            throw new ServerErrorHttpException('Unable to issue login code.');
        }

        return $payload;
    }

    /**
     * Context is server-generated metadata. Keep the v1 issuer allowlist
     * narrow so future callers cannot accidentally persist credentials or
     * unbounded request data in Redis.
     *
     * @param array<string, mixed> $context
     */
    private function validateContext(array $context): void
    {
        if ($context !== [] && array_is_list($context)) {
            throw new ServerErrorHttpException('Unable to issue login code.');
        }

        foreach ($context as $field => $value) {
            if (!is_string($field)
                || !in_array($field, self::CONTEXT_ALLOWED_FIELDS, true)
                || !is_string($value)
                || strlen($value) > self::CONTEXT_MAX_STRING_BYTES) {
                throw new ServerErrorHttpException('Unable to issue login code.');
            }
        }
    }

    private function decodeRecord(string $payload): object
    {
        if (strlen($payload) === 0 || strlen($payload) > self::MAX_RECORD_BYTES) {
            $this->malformedRecord();
        }

        try {
            $record = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->malformedRecord();
        }

        if (!is_object($record)
            || !isset($record->v, $record->user_id, $record->issued_at, $record->expires_at, $record->purpose, $record->issuer, $record->context)
            || !is_int($record->v)
            || $record->v !== self::RECORD_VERSION
            || !is_int($record->user_id)
            || $record->user_id <= 0
            || !is_int($record->issued_at)
            || !is_int($record->expires_at)
            || $record->issued_at <= 0
            || $record->expires_at - $record->issued_at !== LoginCodeSettings::ACTIVE_WINDOW_SECONDS
            || $record->purpose !== self::PURPOSE
            || $record->issuer !== self::ISSUER
            || !is_object($record->context)) {
            $this->malformedRecord();
        }

        return $record;
    }

    private function malformedRecord(): void
    {
        $this->telemetry('malformed');
        Yii::error('Login-code Redis record is malformed.', 'login-code');
        throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
    }

    private function codeKey(string $rawCode): string
    {
        return $this->settings->prefix() . ':code:' . RefreshToken::hashToken($rawCode);
    }

    /**
     * QR URLs historically recognize web_ as an optional transport prefix.
     * Never issue a bearer code with that prefix itself, otherwise a consumer
     * would strip four legitimate code characters before resolving it.
     */
    private function generateRawCode(): string
    {
        for ($attempt = 0; $attempt < self::MAX_CODE_GENERATION_ATTEMPTS; ++$attempt) {
            $rawCode = $this->codeGenerator !== null
                ? call_user_func($this->codeGenerator)
                : Yii::$app->security->generateRandomString(64);

            if (
                is_string($rawCode)
                && preg_match('/^[A-Za-z0-9_-]{64}$/D', $rawCode) === 1
                && !str_starts_with($rawCode, 'web_')
            ) {
                return $rawCode;
            }
        }

        Yii::error('Login-code generator returned an invalid or reserved value repeatedly.', 'login-code');
        throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
    }

    private function redisTimeMilliseconds(): int
    {
        $time = $this->redisCommand('TIME', []);
        $seconds = is_array($time) ? $this->parseRedisInteger($time[0] ?? null) : null;
        $microseconds = is_array($time) ? $this->parseRedisInteger($time[1] ?? null) : null;
        if ($seconds === null || $seconds < 0 || $microseconds === null || $microseconds < 0 || $microseconds >= 1000000) {
            $this->redisProtocolFailure('Login-code Redis TIME response is invalid.');
        }

        return ($seconds * 1000) + intdiv($microseconds, 1000);
    }

    /** @param mixed $value */
    private function parseRedisInteger($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int)$value;
        }

        return null;
    }

    private function redisProtocolFailure(string $message): void
    {
        $this->telemetry('redis_error');
        Yii::error($message, 'login-code');
        throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
    }

    private function assertRedisReady(): void
    {
        if ($this->readiness === null) {
            if (Yii::$app->has('loginCodeReadiness')) {
                $configured = Yii::$app->get('loginCodeReadiness');
                if ($configured instanceof LoginCodeReadiness) {
                    $this->readiness = $configured;
                }
            }

            if ($this->readiness === null) {
                $this->readiness = new LoginCodeReadiness([
                    'settings' => $this->settings,
                    'redis' => $this->redis,
                ]);
            }
        }

        $this->readiness->assertReady();
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    private function redisCommand(string $command, array $arguments)
    {
        try {
            return $this->redis()->executeCommand($command, $arguments);
        } catch (Throwable $exception) {
            $this->telemetry('redis_error');
            Yii::error('Login-code Redis command failed.', 'login-code');
            throw new ServiceUnavailableHttpException('Login code storage is temporarily unavailable.');
        }
    }

    /** @return mixed */
    private function redis()
    {
        if ($this->redis === null) {
            $this->redis = Yii::$app->get('redis');
        }

        return $this->redis;
    }

    /** @param mixed $response */
    private function setSucceeded($response): bool
    {
        return $response === true || $response === 'OK' || (is_object($response) && (string)$response === 'OK');
    }

    private function legacyTimestamp(string $createdAt): int
    {
        $timestamp = strtotime($createdAt);
        return $timestamp === false ? 0 : $timestamp;
    }

    /**
     * Yii DB command logging/profiling expands bound parameters with raw SQL.
     * Login-code lookup/write parameters include bearer-code-derived data, so
     * disable both only for the narrow credential-bearing command and restore
     * the connection before returning or throwing.
     *
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    private function withoutLoginCodeDbLogging(callable $operation)
    {
        $db = Yii::$app->db;
        $logging = $db->enableLogging;
        $profiling = $db->enableProfiling;
        $db->enableLogging = false;
        $db->enableProfiling = false;

        try {
            return $operation();
        } finally {
            $db->enableLogging = $logging;
            $db->enableProfiling = $profiling;
        }
    }

    private function telemetry(string $event): void
    {
        LoginCodeTelemetry::record($event, $this->telemetrySource);
    }
}

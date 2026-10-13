<?php
declare(strict_types=1);

namespace api\modules\v1\services;

use api\modules\v1\components\DeviceSnAuthContext;
use api\modules\v1\models\User;
use api\modules\v1\RefreshToken;
use app\components\LegacyArRefreshContinuity;
use common\components\security\ServiceUnavailableHttpException;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

// This dependency-free file is byte-identical to the A1 engine. Its namespace
// deliberately stays the same so that both adapters execute the same protocol.
require_once dirname(__DIR__) . '/components/LegacyArRefreshContinuity.php';

/** Temporary legacy writer baseline; never used when Identity owns accounts. */
class LegacyArContinuityAdapter
{
    public static function enabled(): bool
    {
        $value = getenv('AR_REFRESH_CONTINUITY_ENABLED');
        if ($value === false || $value === '') {
            return false;
        }
        $enabled = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($enabled === null) {
            throw new ServiceUnavailableHttpException('Legacy refresh configuration is invalid.');
        }
        if ($enabled && IndependentIdentity::enabled()) {
            throw new ServiceUnavailableHttpException('Legacy refresh writers are retired.');
        }
        return $enabled;
    }

    public function issue(User $expected, array $context): array
    {
        $auth = DeviceSnAuthContext::normalize($expected->authContext ?: $context);
        return $this->transport(function () use ($expected, $context, $auth): array {
            return $this->locked((int)$expected->id, function (User $current, array $account) use ($expected, $context, $auth): array {
                // Password verification precedes this lock. Revalidate its inputs.
                if ($current->password_hash !== $expected->password_hash || $current->auth_key !== $expected->auth_key) {
                    throw new UnauthorizedHttpException('Account credentials changed.');
                }
                $fields = $this->fields($context, $auth);
                return $this->engine()->issue($account,
                    fn($raw, $source, $now) => $this->token($current, $raw, $source, $now, $fields), $fields);
            });
        });
    }

    public function refresh(string $raw, array $context): array
    {
        return $this->transport(function () use ($raw, $context): array {
            $engine = $this->engine();
            $userId = $engine->credentialUserId($raw);
            $endpoint = $context['refresh_endpoint'] ?? '/v1/auth/refresh';
            if (!in_array($endpoint, ['/v1/auth/refresh'], true)) {
                throw new ServiceUnavailableHttpException('Legacy refresh endpoint is invalid.');
            }
            $key = $context['idempotency_key'] ?? null;
            if ($key !== null && !is_string($key)) {
                throw new BadRequestHttpException('Invalid idempotency key.');
            }
            return $this->locked($userId, function (User $user, array $account) use ($engine, $raw, $context, $endpoint, $key): array {
                // Provenance comes only from the reviewed predecessor, never the
                // public context. It is copied by the engine and checked live.
                $fields = $this->fields($context, $this->source($raw));
                $body = $engine->refresh($raw, $account, $endpoint, $key,
                    fn($successor, $auth, $now) => ['success' => true, 'message' => 'refresh',
                        'token' => $this->token($user, $successor, $auth, $now, $fields)], $fields);
                return $body['token'];
            });
        });
    }

    public function revoke(string $raw): bool
    {
        return $this->transport(function () use ($raw): bool {
            $engine = $this->engine();
            $userId = $engine->credentialUserId($raw);
            return $this->locked($userId, function (User $user, array $account) use ($engine, $raw): bool {
                $engine->revoke($raw, $account);
                return true;
            });
        });
    }

    public function revokeUser(int $userId): int
    {
        return $this->transport(fn() => $this->locked($userId, function (User $user, array $account): int {
            $this->engine()->revokeUser($account);
            // The persistent epoch also fences consumed predecessors and replay
            // caches, which no longer have an ActiveRecord row to delete.
            return (int)RefreshToken::deleteAll(['user_id' => $user->id]);
        }));
    }

    /** A known/partially prepared AR credential must never enter QR fallback. */
    public function hasCredential(string $raw): bool
    {
        return $this->transport(function () use ($raw): bool {
            $key = LegacyArRefreshContinuity::PREFIX . 'byHash:' . hash('sha256', $raw);
            if (Yii::$app->redis->executeCommand('TYPE', [$key]) !== 'none') {
                return true;
            }
            return RefreshToken::find()->where(['key' => [RefreshToken::hashToken($raw), $raw]])->exists();
        });
    }

    protected function engine(): LegacyArRefreshContinuity
    {
        if ((Yii::$app->redis->retries ?? null) !== 0) {
            throw new ServiceUnavailableHttpException('Legacy refresh Redis retries must be disabled.');
        }
        $encoded = getenv('AR_REFRESH_REPLAY_KEY_BASE64');
        $key = is_string($encoded) ? base64_decode($encoded, true) : false;
        if ($key === false || strlen($key) !== 32 || base64_encode($key) !== $encoded) {
            throw new ServiceUnavailableHttpException('Legacy refresh stable key is not configured.');
        }
        return new LegacyArRefreshContinuity(
            static fn($command, $args) => Yii::$app->redis->executeCommand($command, $args), $key, 'server',
            function (int $userId, array $auth): bool {
                (new DeviceSnService())->authorizeSession($auth['device_sn_id'], $userId);
                return true;
            }
        );
    }

    protected function locked(int $userId, callable $operation): mixed
    {
        if (!self::enabled()) {
            throw new ServiceUnavailableHttpException('Legacy continuity is disabled.');
        }
        if ($userId <= 0) {
            throw new UnauthorizedHttpException('Account is unavailable.');
        }
        $db = Yii::$app->db;
        if ($db->driverName !== 'mysql') {
            throw new ServiceUnavailableHttpException('Legacy continuity requires MySQL row locks.');
        }
        $retrying = property_exists($db, 'maxRetries');
        $previousRetries = $retrying ? $db->maxRetries : null;
        $transaction = null;
        $owned = false;
        try {
            // CynosDbCommand normally retries on cold-start errors. A lock
            // operation must not silently execute again on another connection.
            if ($retrying) {
                $db->maxRetries = 1;
            }
            $existing = $db->getTransaction();
            $owned = $existing === null || !$existing->isActive;
            $transaction = $owned ? $db->beginTransaction(\yii\db\Transaction::READ_COMMITTED) : $existing;
            $row = $db->createCommand('SELECT * FROM `user` WHERE id=:id FOR UPDATE', [':id' => $userId])->queryOne();
            if (!$row) {
                throw new UnauthorizedHttpException('Account is unavailable.');
            }
            $epoch = $db->createCommand('SELECT CAST(epoch AS CHAR) FROM identity_legacy_security_versions WHERE user_id=:id FOR SHARE',
                [':id' => $userId])->queryScalar();
            if (!is_string($epoch) || !preg_match('/^[1-9][0-9]{0,19}$/D', $epoch)
                || (strlen($epoch) === 20 && strcmp($epoch, '18446744073709551615') > 0)) {
                throw new ServiceUnavailableHttpException('Account security version is not prepared.');
            }
            $user = new User();
            User::populateRecord($user, $row);
            $account = ['id' => (int)$row['id'], 'status' => (int)$row['status'],
                'passwordHash' => $row['password_hash'] === null ? null : (string)$row['password_hash'],
                'authKey' => $row['auth_key'] === null ? null : (string)$row['auth_key'], 'accountEpoch' => $epoch];
            $result = $operation($user, $account);
            if ($owned) {
                $transaction->commit();
            }
            return $result;
        } catch (\Throwable $exception) {
            if ($owned && $transaction !== null && $transaction->isActive) {
                $transaction->rollBack();
            }
            throw $exception;
        } finally {
            if ($retrying) {
                $db->maxRetries = $previousRetries;
            }
        }
    }

    private function fields(array $context, array $auth): array
    {
        $clock = Yii::$app->redis->executeCommand('TIME');
        if (!is_array($clock) || count($clock) !== 2 || !ctype_digit((string)$clock[0]) || !ctype_digit((string)$clock[1])) {
            throw new ServiceUnavailableHttpException('Legacy refresh clock is unavailable.');
        }
        $seconds = (int)$clock[0];
        $expiry = $seconds + RefreshToken::expirySeconds();
        return ['keyEncoding' => 'sha256', 'issuer' => Yii::$app->request->hostInfo,
            'expiresAtMs' => $expiry * 1000, 'created_at' => (string)$seconds, 'expires_at' => (string)$expiry,
            'session_id' => $context['session_id'] ?? Yii::$app->security->generateRandomString(32),
            'user_agent' => $context['user_agent'] ?? null, 'ip' => $context['ip'] ?? null] + $auth;
    }

    private function source(string $raw): array
    {
        $key = LegacyArRefreshContinuity::PREFIX . 'byHash:' . hash('sha256', $raw);
        $flat = Yii::$app->redis->executeCommand('HMGET', [$key, 'authMethod', 'deviceSnId']);
        if (!is_array($flat) || count($flat) !== 2 || $flat[0] === null || $flat[1] === null) {
            throw new ServiceUnavailableHttpException('Legacy refresh provenance is not prepared.');
        }
        if ($flat === ['', '']) {
            return [];
        }
        return DeviceSnAuthContext::normalize(['auth_method' => $flat[0], 'device_sn_id' => $flat[1]]);
    }

    private function token(User $user, string $raw, array $auth, int $redisTimeMs, array $fields): array
    {
        $now = (new \DateTimeImmutable('@' . intdiv($redisTimeMs, 1000)))->setTimezone(new \DateTimeZone(Yii::$app->timeZone));
        $expires = $now->modify('+3 hours');
        return ['accessToken' => $user->generateAccessToken($now, $expires, $fields['session_id'], null, $auth),
            'expires' => $expires->format('Y-m-d H:i:s'), 'refreshToken' => $raw];
    }

    private function transport(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (\yii\web\HttpException $exception) {
            throw $exception;
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() === 401) {
                throw new UnauthorizedHttpException('Refresh token is unavailable.', 0, $exception);
            }
            if ($exception->getCode() === 400) {
                throw new BadRequestHttpException('Invalid refresh request.', 0, $exception);
            }
            throw new ServiceUnavailableHttpException('Legacy refresh is temporarily unavailable.', 0, $exception);
        } catch (\Throwable $exception) {
            throw new ServiceUnavailableHttpException('Legacy refresh is temporarily unavailable.', 0, $exception);
        }
    }
}

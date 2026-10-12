<?php

namespace api\modules\v1\services;

use api\modules\v1\models\User;
use Yii;

/** Temporary, opt-in association evidence; never infer an anonymous actor. */
final class LegacyEmailChallengeRecorder
{
    private const ACTIVE_USER_STATUS = 10;
    public static function enabled(): bool
    {
        return !IndependentIdentity::enabled()
            && in_array(strtolower(getenv('IDENTITY_CAPTURE_LEGACY_EMAIL_CHALLENGES') ?: 'false'), ['1', 'true', 'yes', 'on'], true);
    }

    public static function key(string $email): string
    {
        return 'identity:legacy_email_challenge:' . hash('sha256', strtolower(trim($email)));
    }

    public static function store($redis, string $codeKey, string $email, string $payload, int $ttl, string $code, int $issuedAt): void
    {
        if (!self::enabled()) throw new \LogicException('Legacy email association capture is not enabled.');
        $identity = Yii::$app->has('user') ? Yii::$app->user->getIdentity() : null;
        $association = '';
        if ($identity instanceof User && (int)$identity->id > 0 && (int)$identity->status === self::ACTIVE_USER_STATUS) {
            $accountEmail = strtolower(trim((string)$identity->email));
            if ($accountEmail === '') $accountEmail = null;
            $association = json_encode(['user_id' => (int)$identity->id, 'account_email' => $accountEmail,
                'target_email' => strtolower(trim($email)), 'code_hash' => hash('sha256', $code),
                'issued_at' => $issuedAt, 'issuer' => 'yii-email-code-v1'], JSON_THROW_ON_ERROR);
        }
        // Redis 7+ PEXPIRETIME gives the exact original deadline. Probe it
        // before changing any key so unsupported servers cannot issue a code
        // with missing evidence. One script keeps overwrites and evidence in
        // sync; anonymous issuance explicitly removes an older association.
        $redis->executeCommand('EVAL', [<<<'LUA'
redis.call('PEXPIRETIME', KEYS[1])
redis.call('SETEX', KEYS[1], ARGV[2], ARGV[1])
redis.call('DEL', KEYS[2])
local deadline = redis.call('PEXPIRETIME', KEYS[1])
if deadline <= 0 then return redis.error_reply('Legacy email challenge has no exact expiry') end
if ARGV[3] ~= '' then
    local association = cjson.decode(ARGV[3])
    association.expires_at_ms = deadline
    redis.call('SET', KEYS[2], cjson.encode(association))
    redis.call('PEXPIREAT', KEYS[2], deadline)
end
return deadline
LUA
            , 2, $codeKey, self::key($email), $payload, $ttl, $association]);
    }

    public static function removeFailedDelivery($redis, string $codeKey, string $email, string $payload): void
    {
        // A slow failed mail delivery must not delete a newer issued code.
        $redis->executeCommand('EVAL', [<<<'LUA'
if redis.call('GET', KEYS[1]) == ARGV[1] then
    return redis.call('DEL', KEYS[1], KEYS[2])
end
return 0
LUA
            , 2, $codeKey, self::key($email), $payload]);
    }
}

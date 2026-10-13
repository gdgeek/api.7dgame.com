<?php
declare(strict_types=1);

namespace app\components;

use RuntimeException;

/** Shared by A1 and server. No Yii, SQL, model, clock or environment dependency. */
final class LegacyArRefreshContinuity
{
    public const PREFIX = 'identity:legacy-refresh:ar:v1:';
    public const VERSION = 'legacy-ar-refresh-continuity-v1';
    public const RESPONSE_VERSION = 'legacy-ar-refresh-response-v1';
    private const META = ['v','hash','recordId','userId','familyId','epoch','accountEpoch','fingerprint','expiresAtMs','recordKeyEncoding','recordSha256','predecessorHash','authMethod','deviceSnId','consumedBy','consumedAtMs','serverSuccessorHash','reviewSha256'];
    private const FAMILY = ['v','userId','epoch','accountEpoch','fingerprint','revoked','revokedAtMs'];
    private const CACHE = ['v','responseVersion','consumer','endpoint','issuer','predecessorHash','successorHash','userId','familyId','epoch','accountEpoch','fingerprint','requestHash','createdAtMs','expiresAtMs','encryptedResponse'];
    private $execute;
    private $verifyProvenance;

    public function __construct(callable $execute, private string $key, private string $consumer, ?callable $verifyProvenance = null)
    {
        if (strlen($key) !== 32 || !in_array($consumer, ['a1','server'], true)) self::fail('CONFIGURATION', 503);
        $this->execute = $execute;
        $this->verifyProvenance = $verifyProvenance;
    }

    public static function fingerprint(?string $passwordHash, ?string $authKey): string
    {
        return hash('sha256', self::json(['yii-ar-account-v1', $passwordHash, $authKey]));
    }

    public function credentialUserId(string $raw): int
    {
        $this->raw($raw);
        $this->ready();
        $meta = $this->metadata(hash('sha256', $raw));
        return self::positive($meta['userId']);
    }

    public function issue(array $account, callable $buildResponse, array $recordFields = []): array
    {
        $account = $this->account($account);
        $snapshots = [$this->ready()];
        $epoch = $this->epoch($account['id'], $snapshots, true);
        $familyId = bin2hex(random_bytes(16));
        $family = ['v'=>'1','userId'=>(string)$account['id'],'epoch'=>$epoch,'accountEpoch'=>$account['accountEpoch'],
            'fingerprint'=>$account['fingerprint'],'revoked'=>'0','revokedAtMs'=>''];
        $familyKey = self::PREFIX . 'family:' . $familyId;
        $snapshots[] = $this->absent($familyKey);
        $snapshots[] = $this->typed($familyKey . ':members', 'none');
        $now = $this->now();
        [$raw, $meta, $record, $allocation] = $this->successor($account, $familyId, $epoch, '', $recordFields, $now, $snapshots);
        $body = $this->body($buildResponse($raw, $this->provenance($meta, $account), $now));
        $writes = $this->newCredentialWrites($meta, $record, $allocation);
        $writes[] = ['op'=>'hash','key'=>$familyKey,'value'=>$family];
        if ($this->get(self::PREFIX . 'user:' . $account['id'] . ':epoch') === null) {
            $writes[] = ['op'=>'string','key'=>self::PREFIX . 'user:' . $account['id'] . ':epoch','value'=>$epoch];
        }
        $this->commit($snapshots, $writes, $this->deadlines($meta));
        return $body;
    }

    public function refresh(string $raw, array $account, string $endpoint, ?string $idempotencyKey, callable $buildResponse, array $recordFields = []): array
    {
        $this->raw($raw);
        if (!preg_match('~^/[a-zA-Z0-9/_-]{1,160}$~D', $endpoint)) self::fail('ENDPOINT', 503);
        $requestHash = $idempotencyKey === null ? 'none' : $this->requestHash($idempotencyKey);
        $account = $this->account($account);
        $snapshots = [$this->ready()];
        $hash = hash('sha256', $raw);
        $meta = $this->metadata($hash, $snapshots);
        $family = $this->liveFamily($meta, $account, $snapshots);
        $this->provenance($meta, $account);
        $now = $this->now();
        $this->notExpired($meta, $now);
        $record = $this->record($meta, $raw, $snapshots, $this->consumer === 'server' && $meta['consumedBy'] === 'server');
        // Original A1 lookup compares raw key, whereas server accepts sha256 or raw.
        if ($this->consumer === 'a1' && $meta['recordKeyEncoding'] !== 'plain') self::fail('A1_KEY_LOOKUP', 401);
        $issuer = $recordFields['issuer'] ?? null;
        if (!is_string($issuer) || strlen($issuer) > 512 || !preg_match('~^https?://[^\s\x00-\x1f\x7f]+$~D', $issuer)) self::fail('ISSUER', 503);
        $cacheKey = self::PREFIX . 'cache:' . $this->consumer . ':' . hash('sha256', $endpoint) . ':' . $hash . ':' . $requestHash;
        $cache = $this->hash($cacheKey);
        if ($cache !== []) {
            $cache = $this->exact($cache, self::CACHE);
            if ($cache['consumer'] !== $this->consumer || $cache['endpoint'] !== $endpoint || $cache['issuer'] !== $issuer
                || $cache['predecessorHash'] !== $hash || $cache['requestHash'] !== $requestHash
                || $cache['familyId'] !== $meta['familyId'] || $cache['userId'] !== $meta['userId']
                || $cache['epoch'] !== $meta['epoch'] || $cache['accountEpoch'] !== $meta['accountEpoch']
                || $cache['fingerprint'] !== $meta['fingerprint'] || $cache['v'] !== '1'
                || $cache['responseVersion'] !== self::RESPONSE_VERSION) self::fail('CACHE_SCOPE', 503);
            $expires = self::positive($cache['expiresAtMs']);
            $created = self::positive($cache['createdAtMs']);
            if ($expires > $created + ($requestHash === 'none' ? 5000 : 30000)) self::fail('CACHE_DEADLINE', 503);
            if ($now < $expires) {
                $snapshots[] = $this->snapshotHash($cacheKey, $cache);
                $successor = $this->metadata($cache['successorHash'], $snapshots);
                $this->liveFamily($successor, $account, $snapshots);
                $this->provenance($successor, $account);
                if ($successor['predecessorHash'] !== $hash || $successor['familyId'] !== $meta['familyId']) self::fail('CACHE_SUCCESSOR', 503);
                if ($this->consumer === 'server' && ($meta['consumedBy'] !== 'server' || $meta['serverSuccessorHash'] !== $successor['hash'])) self::fail('CONSUMPTION', 401);
                $this->notExpired($successor, $now);
                $this->record($successor, null, $snapshots, false);
                $body = $this->decrypt($cache);
                $this->commit($snapshots, [], array_merge([$expires], $this->deadlines($meta, $successor)));
                return $body;
            }
            // Redis should expire it at the same absolute cutoff. Never renew it.
            self::fail('CACHE_EXPIRED', 401);
        }
        if ($meta['consumedBy'] !== '' || $record === null) self::fail('CONSUMED', 401);
        $snapshots[] = $this->absent($cacheKey);
        [$newRaw, $successor, $newRecord, $allocation] = $this->successor($account, $meta['familyId'], $meta['epoch'], $hash, $recordFields, $now, $snapshots, $meta);
        $body = $this->body($buildResponse($newRaw, $this->provenance($successor, $account), $now));
        $deadline = $now + ($requestHash === 'none' ? 5000 : 30000);
        foreach ($this->deadlines($meta, $successor) as $limit) $deadline = min($deadline, $limit);
        if ($deadline <= $now) self::fail('EXPIRED', 401);
        $cache = ['v'=>'1','responseVersion'=>self::RESPONSE_VERSION,'consumer'=>$this->consumer,'endpoint'=>$endpoint,'issuer'=>$issuer,
            'predecessorHash'=>$hash,'successorHash'=>$successor['hash'],'userId'=>$meta['userId'],'familyId'=>$meta['familyId'],
            'epoch'=>$meta['epoch'],'accountEpoch'=>$meta['accountEpoch'],'fingerprint'=>$meta['fingerprint'],
            'requestHash'=>$requestHash,'createdAtMs'=>(string)$now,'expiresAtMs'=>(string)$deadline];
        $cache['encryptedResponse'] = $this->encrypt($body, $cache);
        $writes = $this->newCredentialWrites($successor, $newRecord, $allocation);
        $writes[] = ['op'=>'hash','key'=>$cacheKey,'value'=>$cache];
        $writes[] = ['op'=>'expiry','key'=>$cacheKey,'value'=>(string)$deadline];
        if ($this->consumer === 'server') {
            $meta['consumedBy'] = 'server';
            $meta['consumedAtMs'] = (string)$now;
            $meta['serverSuccessorHash'] = $successor['hash'];
            $writes[] = ['op'=>'hash','key'=>self::PREFIX . 'byHash:' . $hash,'value'=>$meta];
            $writes[] = ['op'=>'delete','key'=>'refresh_token:a:' . $meta['recordId']];
            $writes[] = ['op'=>'lrem','key'=>'refresh_token','value'=>$meta['recordId']];
        }
        $this->commit($snapshots, $writes, array_merge([$deadline], $this->deadlines($meta, $successor)));
        return $body;
    }

    public function revoke(string $raw, array $account): void
    {
        $this->raw($raw);
        $account = $this->account($account, false);
        $snapshots = [$this->ready()];
        $meta = $this->metadata(hash('sha256', $raw), $snapshots);
        if ($meta['userId'] !== (string)$account['id']) self::fail('OWNER', 401);
        $key = self::PREFIX . 'family:' . $meta['familyId'];
        $family = $this->exact($this->hash($key), self::FAMILY);
        if ($family['userId'] !== $meta['userId']) self::fail('FAMILY', 503);
        $snapshots[] = $this->snapshotHash($key, $family);
        $family['revoked'] = '1';
        $family['revokedAtMs'] = $family['revokedAtMs'] ?: (string)$this->now();
        $this->commit($snapshots, [['op'=>'hash','key'=>$key,'value'=>$family]], []);
    }

    public function revokeUser(array $account): void
    {
        $account = $this->account($account, false);
        $snapshots = [$this->ready()];
        $epoch = $this->epoch($account['id'], $snapshots, true);
        if ((int)$epoch >= 2147483647) self::fail('EPOCH_CAPACITY', 503);
        $this->commit($snapshots, [['op'=>'string','key'=>self::PREFIX . 'user:' . $account['id'] . ':epoch','value'=>(string)((int)$epoch + 1)]], []);
    }

    /** Offline only. Never invoked by credential refresh or implicitly by lookup. */
    public function registerReviewedCredential(string $raw, array $account, array $review): void
    {
        $this->raw($raw);
        $this->registerReviewedHash(hash('sha256', $raw), $raw, $account, $review);
    }

    /** Offline SHA256-keyed AR rows need no unavailable original bearer value. */
    public function registerReviewedRecordHash(string $hash, array $account, array $review): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $hash)
            || ($review['recordKeyEncoding'] ?? null) !== 'sha256'
            || !is_array($review['record'] ?? null) || ($review['record']['key'] ?? null) !== $hash) {
            self::fail('REVIEW_HASH_ENCODING', 503);
        }
        $this->registerReviewedHash($hash, null, $account, $review);
    }

    private function registerReviewedHash(string $hash, ?string $raw, array $account, array $review): void
    {
        $account = $this->account($account);
        $this->keys($review, ['recordId','record','recordKeyEncoding','expiresAtMs','reviewSha256']);
        if (!preg_match('/^[a-f0-9]{64}$/D', (string)$review['reviewSha256']) || !is_array($review['record'])) self::fail('REVIEW', 503);
        $recordId = (string)self::positive((string)$review['recordId']);
        $record = $this->strings($review['record']);
        $key = self::PREFIX . 'byHash:' . $hash;
        $snapshots = [$this->ready(true), $this->absent($key)];
        $live = $this->hash('refresh_token:a:' . $recordId);
        if ($live !== $record) self::fail('REVIEW_RECORD_CHANGED', 503);
        $epoch = $this->epoch($account['id'], $snapshots, true);
        $familyId = bin2hex(random_bytes(16));
        $provenance = $this->recordProvenance($record);
        $meta = ['v'=>'1','hash'=>$hash,'recordId'=>$recordId,'userId'=>(string)$account['id'],'familyId'=>$familyId,
            'epoch'=>$epoch,'accountEpoch'=>$account['accountEpoch'],'fingerprint'=>$account['fingerprint'],
            'expiresAtMs'=>$review['expiresAtMs'] === null ? '' : (string)self::positive((string)$review['expiresAtMs']),
            'recordKeyEncoding'=>$review['recordKeyEncoding'],'recordSha256'=>$this->rowHash($record),'predecessorHash'=>'',
            'authMethod'=>$provenance['authMethod'],'deviceSnId'=>$provenance['deviceSnId'],
            'consumedBy'=>'','consumedAtMs'=>'','serverSuccessorHash'=>'','reviewSha256'=>$review['reviewSha256']];
        $this->provenance($meta, $account);
        $this->record($meta, $raw, $snapshots, false);
        $this->notExpired($meta, $this->now());
        $familyKey = self::PREFIX . 'family:' . $familyId;
        $snapshots[] = $this->absent($familyKey);
        $snapshots[] = $this->typed($familyKey . ':members', 'none');
        $writes = [['op'=>'hash','key'=>$key,'value'=>$meta],
            ['op'=>'hash','key'=>$familyKey,'value'=>['v'=>'1','userId'=>$meta['userId'],'epoch'=>$epoch,'accountEpoch'=>$meta['accountEpoch'],
                'fingerprint'=>$meta['fingerprint'],'revoked'=>'0','revokedAtMs'=>'']],
            ['op'=>'sadd','key'=>$familyKey . ':members','value'=>$hash]];
        if ($this->get(self::PREFIX . 'user:' . $account['id'] . ':epoch') === null) $writes[] = ['op'=>'string','key'=>self::PREFIX . 'user:' . $account['id'] . ':epoch','value'=>$epoch];
        $this->commit($snapshots, $writes, $this->deadlines($meta));
    }

    private function successor(array $account, string $familyId, string $epoch, string $predecessor, array $fields, int $now, array &$snapshots, ?array $parent = null): array
    {
        $allowed = ['keyEncoding','expiresAtMs','issuer','session_id','user_agent','ip','created_at','expires_at','auth_method','device_sn_id'];
        foreach ($fields as $name=>$value) if (!in_array($name, $allowed, true)) self::fail('RECORD_TEMPLATE', 503);
        $encoding = $fields['keyEncoding'] ?? ($this->consumer === 'a1' ? 'plain' : 'sha256');
        if (!in_array($encoding, ['plain','sha256'], true)) self::fail('KEY_ENCODING', 503);
        $expires = $fields['expiresAtMs'] ?? null;
        if ($expires !== null && self::positive((string)$expires) <= $now) self::fail('EXPIRED', 401);
        if ($this->consumer === 'a1' && ($expires !== null || $encoding !== 'plain')) self::fail('A1_PERMANENT_POLICY', 503);
        if ($this->consumer === 'server' && $expires === null) self::fail('SERVER_EXPIRY_REQUIRED', 503);
        $raw = $this->consumer === 'a1' ? rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=') : bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);
        $counter = $this->get('refresh_token:s:id');
        if ($counter !== null && !preg_match('/^(0|[1-9][0-9]{0,9})$/D', $counter)) self::fail('AR_COUNTER', 503);
        if ((int)$counter >= 2147483647) self::fail('AR_COUNTER_CAPACITY', 503);
        $id = (string)((int)$counter + 1);
        $snapshots[] = ['key'=>'refresh_token:s:id','type'=>$counter === null ? 'none' : 'string','value'=>$counter,'persistent'=>$counter!==null];
        $snapshots[] = $this->typed('refresh_token', ['none','list']);
        $snapshots[] = $this->absent('refresh_token:a:' . $id);
        $snapshots[] = $this->absent(self::PREFIX . 'byHash:' . $hash);
        $record = ['id'=>$id,'user_id'=>(string)$account['id'],'key'=>$encoding === 'plain' ? $raw : $hash];
        foreach (['session_id','user_agent','ip','created_at','expires_at','auth_method','device_sn_id'] as $field) {
            if (isset($fields[$field])) $record[$field] = (string)$fields[$field];
        }
        if ($expires !== null && (!isset($record['expires_at']) || !preg_match('/^[1-9][0-9]*$/D', $record['expires_at']) || (int)$record['expires_at'] * 1000 !== (int)$expires)) self::fail('EXPIRY_EVIDENCE', 503);
        $provenance = $this->recordProvenance($record);
        if ($parent !== null && ($provenance['authMethod'] !== $parent['authMethod'] || $provenance['deviceSnId'] !== $parent['deviceSnId'])) self::fail('PROVENANCE_DOWNGRADE', 401);
        $meta = ['v'=>'1','hash'=>$hash,'recordId'=>$id,'userId'=>(string)$account['id'],'familyId'=>$familyId,'epoch'=>$epoch,
            'accountEpoch'=>$account['accountEpoch'],'fingerprint'=>$account['fingerprint'],'expiresAtMs'=>$expires === null ? '' : (string)$expires,
            'recordKeyEncoding'=>$encoding,'recordSha256'=>$this->rowHash($record),'predecessorHash'=>$predecessor,
            'authMethod'=>$provenance['authMethod'],'deviceSnId'=>$provenance['deviceSnId'],
            'consumedBy'=>'','consumedAtMs'=>'','serverSuccessorHash'=>'','reviewSha256'=>''];
        $memberKey = self::PREFIX . 'family:' . $familyId . ':members';
        $snapshots[] = $this->typed($memberKey, ['none','set']);
        return [$raw, $meta, $record, $id];
    }

    private function newCredentialWrites(array $meta, array $record, string $allocation): array
    {
        return [['op'=>'string','key'=>'refresh_token:s:id','value'=>$allocation],
            ['op'=>'hash','key'=>'refresh_token:a:' . $allocation,'value'=>$record],
            ['op'=>'rpush','key'=>'refresh_token','value'=>$allocation],
            ['op'=>'hash','key'=>self::PREFIX . 'byHash:' . $meta['hash'],'value'=>$meta],
            ['op'=>'sadd','key'=>self::PREFIX . 'family:' . $meta['familyId'] . ':members','value'=>$meta['hash']]];
    }

    private function account(array $account, bool $requireActive = true): array
    {
        $this->keys($account, ['id','status','passwordHash','authKey','accountEpoch']);
        if (!is_int($account['id']) || $account['id'] <= 0 || !is_int($account['status'])
            || ($account['passwordHash'] !== null && !is_string($account['passwordHash']))
            || ($account['authKey'] !== null && !is_string($account['authKey']))) self::fail('ACCOUNT', 503);
        if ($requireActive && $account['status'] !== 10) self::fail('ACCOUNT_DISABLED', 401);
        $account['accountEpoch'] = self::accountEpoch((string)$account['accountEpoch']);
        $account['fingerprint'] = self::fingerprint($account['passwordHash'], $account['authKey']);
        return $account;
    }

    private function liveFamily(array $meta, array $account, array &$snapshots): array
    {
        if ($meta['userId'] !== (string)$account['id'] || $meta['fingerprint'] !== $account['fingerprint']
            || $meta['accountEpoch'] !== $account['accountEpoch']) self::fail('ACCOUNT_CHANGED', 401);
        $epoch = $this->epoch($account['id'], $snapshots, false);
        if ($meta['epoch'] !== $epoch) self::fail('USER_REVOKED', 401);
        $key = self::PREFIX . 'family:' . $meta['familyId'];
        $family = $this->exact($this->hash($key), self::FAMILY);
        if ($family['v'] !== '1' || $family['userId'] !== $meta['userId'] || $family['epoch'] !== $epoch
            || $family['accountEpoch'] !== $meta['accountEpoch'] || $family['fingerprint'] !== $meta['fingerprint']) self::fail('FAMILY', 503);
        if ($family['revoked'] !== '0' || $family['revokedAtMs'] !== '') self::fail('FAMILY_REVOKED', 401);
        $snapshots[] = $this->snapshotHash($key, $family);
        return $family;
    }

    private function record(array $meta, ?string $raw, array &$snapshots, bool $allowConsumed): ?array
    {
        $key = 'refresh_token:a:' . $meta['recordId'];
        $record = $this->hash($key);
        if ($record === []) {
            $snapshots[] = $this->absent($key);
            if ($allowConsumed && $meta['consumedBy'] === 'server') return null;
            self::fail('AR_MISSING', 401);
        }
        if ($meta['consumedBy'] !== '') self::fail('AR_CONSUMED', 401);
        if (($record['id'] ?? '') !== $meta['recordId'] || ($record['user_id'] ?? '') !== $meta['userId']
            || !empty($record['revoked_at'])) self::fail('AR_REVOKED', 401);
        if (!in_array($meta['recordKeyEncoding'], ['plain','sha256'], true)) self::fail('KEY_ENCODING', 503);
        $keyHash = $meta['recordKeyEncoding'] === 'plain' ? hash('sha256', $record['key'] ?? '') : ($record['key'] ?? '');
        if ($keyHash !== $meta['hash'] || ($raw !== null && $meta['recordKeyEncoding'] === 'plain' && ($record['key'] ?? '') !== $raw)) self::fail('AR_KEY', 401);
        if ($this->rowHash($record) !== $meta['recordSha256']) self::fail('AR_CHANGED', 401);
        $provenance = $this->recordProvenance($record);
        if ($provenance['authMethod'] !== $meta['authMethod'] || $provenance['deviceSnId'] !== $meta['deviceSnId']) self::fail('AR_PROVENANCE', 401);
        if (isset($record['expires_at']) && $record['expires_at'] !== '') {
            if ((string)(self::positive($record['expires_at']) * 1000) !== $meta['expiresAtMs']) self::fail('AR_EXPIRY', 401);
        } elseif ($meta['expiresAtMs'] !== '') self::fail('AR_EXPIRY_EVIDENCE', 503);
        $snapshots[] = $this->snapshotHash($key, $record);
        return $record;
    }

    private function metadata(string $hash, ?array &$snapshots = null): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $hash)) self::fail('HASH', 503);
        $key = self::PREFIX . 'byHash:' . $hash;
        $meta = $this->exact($this->hash($key), self::META);
        if ($meta['v'] !== '1' || $meta['hash'] !== $hash || !preg_match('/^[a-f0-9]{32}$/D', $meta['familyId'])
            || !preg_match('/^[a-f0-9]{64}$/D', $meta['fingerprint']) || !preg_match('/^[a-f0-9]{64}$/D', $meta['recordSha256'])
            || !in_array($meta['consumedBy'], ['', 'server'], true)) self::fail('METADATA', 503);
        foreach (['recordId','userId','epoch'] as $field) self::positive($meta[$field]);
        self::accountEpoch($meta['accountEpoch']);
        if (($meta['consumedBy'] === '') !== ($meta['consumedAtMs'] === '' && $meta['serverSuccessorHash'] === '')) self::fail('CONSUMPTION_METADATA', 503);
        if ($meta['consumedBy'] === 'server' && (!preg_match('/^[a-f0-9]{64}$/D', $meta['serverSuccessorHash']) || self::positive($meta['consumedAtMs']) < 1)) self::fail('CONSUMPTION_METADATA', 503);
        if ($snapshots !== null) $snapshots[] = $this->snapshotHash($key, $meta);
        return $meta;
    }

    private function provenance(array $meta, array $account): array
    {
        if ($meta['authMethod'] === '' && $meta['deviceSnId'] === '') return [];
        if ($meta['authMethod'] !== 'device_sn' || !preg_match('/^[1-9][0-9]*$/D', $meta['deviceSnId'])) self::fail('PROVENANCE', 401);
        $provenance = ['auth_method'=>'device_sn','device_sn_id'=>self::positive($meta['deviceSnId'])];
        if ($this->verifyProvenance === null || ($this->verifyProvenance)($account['id'], $provenance) !== true) self::fail('PROVENANCE_UNVERIFIED', 401);
        return $provenance;
    }

    private function recordProvenance(array $record): array
    {
        $method = $record['auth_method'] ?? '';
        $id = $record['device_sn_id'] ?? '';
        if ($method === '' && $id === '') return ['authMethod'=>'','deviceSnId'=>''];
        if ($method !== 'device_sn' || !preg_match('/^[1-9][0-9]*$/D', $id)) self::fail('PROVENANCE', 401);
        self::positive($id);
        return ['authMethod'=>$method,'deviceSnId'=>$id];
    }

    private function epoch(int $userId, array &$snapshots, bool $allowNew): string
    {
        $key = self::PREFIX . 'user:' . $userId . ':epoch';
        $value = $this->get($key);
        if ($value === null && !$allowNew) self::fail('EPOCH_MISSING', 503);
        if ($value !== null) self::positive($value);
        $snapshots[] = ['key'=>$key,'type'=>$value === null ? 'none' : 'string','value'=>$value,'persistent'=>$value!==null];
        return $value ?? '1';
    }

    private function ready(bool $offlineReview = false): array
    {
        $key = self::PREFIX . 'ready';
        $ready = $this->exact($this->hash($key), ['v','protocol','consumers','reviewSha256','state']);
        if ($ready['v'] !== '1' || $ready['protocol'] !== self::VERSION || $ready['consumers'] !== 'a1,server'
            || !in_array($ready['state'], $offlineReview ? ['preparing','ready'] : ['ready'],true)
            || !preg_match('/^[a-f0-9]{64}$/D', $ready['reviewSha256'])) self::fail('NOT_READY', 503);
        return $this->snapshotHash($key, $ready);
    }

    private function body(mixed $body): array
    {
        if (!is_array($body) || strlen(self::json($body)) > 262144) self::fail('RESPONSE', 503);
        return $body;
    }

    private function aad(array $cache): string
    {
        return self::json([self::RESPONSE_VERSION, $cache['consumer'], $cache['endpoint'], $cache['issuer'],
            $cache['predecessorHash'], $cache['successorHash'], $cache['userId'], $cache['familyId'],
            $cache['epoch'], $cache['accountEpoch'], $cache['fingerprint'], $cache['requestHash'], $cache['createdAtMs'], $cache['expiresAtMs']]);
    }

    private function encrypt(array $body, array $cache): string
    {
        $nonce = random_bytes(12);
        $tag = '';
        $encrypted = openssl_encrypt(self::json($body), 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $nonce, $tag, $this->aad($cache), 16);
        if ($encrypted === false || strlen($tag) !== 16) self::fail('ENCRYPTION', 503);
        return base64_encode($nonce . $tag . $encrypted);
    }

    private function decrypt(array $cache): array
    {
        if(strlen($cache['encryptedResponse'])>349568) self::fail('CACHE_CIPHERTEXT',503);
        $bytes = base64_decode($cache['encryptedResponse'], true);
        if ($bytes === false || strlen($bytes) < 28 || strlen($bytes) > 262172) self::fail('CACHE_CIPHERTEXT', 503);
        $plain = openssl_decrypt(substr($bytes, 28), 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, substr($bytes, 0, 12), substr($bytes, 12, 16), $this->aad($cache));
        if ($plain === false) self::fail('CACHE_AUTHENTICATION', 503);
        try { return $this->body(json_decode($plain, true, 64, JSON_THROW_ON_ERROR)); }
        catch (\JsonException $e) { self::fail('CACHE_RESPONSE', 503); }
    }

    private function requestHash(string $key): string
    {
        if ($key === '' || strlen($key) > 256 || preg_match('/[\x00-\x1f\x7f]/', $key)) self::fail('IDEMPOTENCY_KEY', 400);
        return hash('sha256', $key);
    }

    private function raw(string $raw): void
    {
        if ($raw === '' || strlen($raw) > 4096 || preg_match('/[\x00-\x20\x7f]/', $raw)) self::fail('CREDENTIAL', 400);
    }

    private function deadlines(array ...$metas): array
    {
        $deadlines = [];
        foreach ($metas as $meta) if ($meta['expiresAtMs'] !== '') $deadlines[] = self::positive($meta['expiresAtMs']);
        return $deadlines;
    }

    private function notExpired(array $meta, int $now): void
    {
        if ($meta['expiresAtMs'] !== '' && self::positive($meta['expiresAtMs']) <= $now) self::fail('EXPIRED', 401);
    }

    private function rowHash(array $record): string
    {
        ksort($record, SORT_STRING);
        return hash('sha256', self::json($record));
    }

    private function now(): int
    {
        $time = $this->command('TIME');
        if (!is_array($time) || count($time) !== 2) self::fail('REDIS_TIME', 503);
        return (int)$time[0] * 1000 + intdiv((int)$time[1], 1000);
    }

    private function get(string $key): ?string
    {
        $this->typed($key, ['none','string']);
        $value = $this->command('GET', [$key]);
        if ($value!==null && $value!==false && (int)$this->command('PTTL',[$key])!==-1) self::fail('PERSISTENT_KEY_TTL',503);
        return $value === null || $value === false ? null : (string)$value;
    }

    private function hash(string $key): array
    {
        $this->typed($key, ['none','hash']);
        $flat = $this->command('HGETALL', [$key]);
        if (!is_array($flat) || count($flat) > 128 || count($flat) % 2 !== 0) self::fail('REDIS_HASH', 503);
        $hash = [];
        $bytes=0;
        for ($i=0; $i<count($flat); $i+=2) {
            $bytes+=strlen((string)$flat[$i])+strlen((string)$flat[$i+1]);
            if($bytes>524288) self::fail('REDIS_HASH_BOUNDS',503);
            $hash[(string)$flat[$i]] = (string)$flat[$i+1];
        }
        ksort($hash, SORT_STRING);
        return $hash;
    }

    private function exact(array $record, array $names): array
    {
        $this->keys($record, $names);
        return $record;
    }

    private function keys(array $value, array $keys): void
    {
        $actual = array_keys($value); sort($actual); sort($keys);
        if ($actual !== $keys) self::fail('FIELDS_OR_UNREVIEWED_METADATA', 503);
    }

    private function strings(array $value): array
    {
        foreach ($value as $k=>$v) {
            if (!is_string($k) || (!is_string($v) && !is_int($v))) self::fail('RECORD_FIELDS', 503);
            $value[$k] = (string)$v;
        }
        ksort($value, SORT_STRING);
        return $value;
    }

    private function typed(string $key, string|array $allowed): array
    {
        $type = $this->command('TYPE', [$key]);
        if (!is_string($type) || !in_array($type, (array)$allowed, true)) self::fail('REDIS_KEY_TYPE', 503);
        $persistent=$type!=='none' && !str_starts_with($key,self::PREFIX.'cache:');
        if($persistent && (int)$this->command('PTTL',[$key])!==-1) self::fail('PERSISTENT_KEY_TTL',503);
        return ['key'=>$key,'type'=>$type,'value'=>null,'persistent'=>$persistent];
    }

    private function absent(string $key): array { return $this->typed($key, 'none'); }
    private function snapshotHash(string $key, array $hash): array
    {
        $persistent=!str_starts_with($key,self::PREFIX . 'cache:');
        if($persistent && (int)$this->command('PTTL',[$key])!==-1) self::fail('PERSISTENT_KEY_TTL',503);
        return ['key'=>$key,'type'=>'hash','value'=>$hash,'persistent'=>$persistent];
    }

    private function command(string $command, array $args = []): mixed
    {
        try { return ($this->execute)($command, $args); }
        catch (\Throwable $e) { throw new RuntimeException('LEGACY_AR_REDIS_UNAVAILABLE_OR_RESULT_UNKNOWN', 503, $e); }
    }

    /** One EVAL only. No retry on CAS conflict or ambiguous acknowledgement. */
    private function commit(array $snapshots, array $writes, array $deadlines): void
    {
        $unique = [];
        foreach ($snapshots as $snapshot) {
            $key = $snapshot['key'];
            if (isset($unique[$key]) && $unique[$key] !== $snapshot) self::fail('REDIS_SNAPSHOT_CHANGED', 503);
            $unique[$key] = $snapshot;
        }
        $result = $this->command('EVAL', [self::LUA, 0, self::json(['snapshots'=>array_values($unique),'writes'=>$writes,'deadlines'=>$deadlines])]);
        if ($result !== 'OK') self::fail(is_string($result) ? $result : 'REDIS_RESULT_UNKNOWN', $result === 'EXPIRED' ? 401 : 503);
    }

    private static function positive(string $value): int
    {
        if (!preg_match('/^[1-9][0-9]{0,15}$/D', $value) || strlen($value) > 15 || (int)$value > 9007199254740991) self::fail('POSITIVE_INTEGER', 503);
        return (int)$value;
    }
    private static function accountEpoch(string $value): string
    {
        if (!preg_match('/^[1-9][0-9]{0,19}$/D',$value) || (strlen($value)===20 && strcmp($value,'18446744073709551615')>0)) self::fail('ACCOUNT_EPOCH',503);
        return $value;
    }
    private static function json(mixed $value): string { return json_encode($value, JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_LINE_TERMINATORS); }
    private static function fail(string $reason, int $code): never { throw new RuntimeException('LEGACY_AR_' . $reason, $code); }

    private const LUA = <<<'LUA'
local p = cjson.decode(ARGV[1])
for _,s in ipairs(p.snapshots) do
  local t = redis.call('TYPE',s.key).ok
  if t ~= s.type then return 'CAS_TYPE_CHANGED' end
  if s.persistent == true and redis.call('PTTL',s.key) ~= -1 then return 'CAS_TTL_CHANGED' end
  if s.type == 'string' and redis.call('GET',s.key) ~= s.value then return 'CAS_CHANGED' end
  if s.type == 'hash' then
    local count = 0
    for k,v in pairs(s.value) do
      count = count + 1
      if redis.call('HGET',s.key,k) ~= v then return 'CAS_CHANGED' end
    end
    if redis.call('HLEN',s.key) ~= count then return 'CAS_CHANGED' end
  end
end
local time = redis.call('TIME')
local now = tonumber(time[1])*1000 + math.floor(tonumber(time[2])/1000)
for _,deadline in ipairs(p.deadlines) do if now >= tonumber(deadline) then return 'EXPIRED' end end
-- Every destination type is validated before the first write: Lua is not rollback.
for _,w in ipairs(p.writes) do
  local t = redis.call('TYPE',w.key).ok
  if w.op == 'hash' and t ~= 'none' and t ~= 'hash' then return 'WRITE_TYPE_CHANGED' end
  if (w.op == 'rpush' or w.op == 'lrem') and t ~= 'none' and t ~= 'list' then return 'WRITE_TYPE_CHANGED' end
  if w.op == 'sadd' and t ~= 'none' and t ~= 'set' then return 'WRITE_TYPE_CHANGED' end
  if w.op == 'string' and t ~= 'none' and t ~= 'string' then return 'WRITE_TYPE_CHANGED' end
end
for _,w in ipairs(p.writes) do
  if w.op == 'string' then redis.call('SET',w.key,w.value)
  elseif w.op == 'hash' then for k,v in pairs(w.value) do redis.call('HSET',w.key,k,v) end
  elseif w.op == 'rpush' then redis.call('RPUSH',w.key,w.value)
  elseif w.op == 'lrem' then redis.call('LREM',w.key,0,w.value)
  elseif w.op == 'sadd' then redis.call('SADD',w.key,w.value)
  elseif w.op == 'delete' then redis.call('DEL',w.key)
  elseif w.op == 'expiry' then redis.call('PEXPIREAT',w.key,w.value)
  else return redis.error_reply('UNREVIEWED_WRITE_OPERATION') end
end
return 'OK'
LUA;
}

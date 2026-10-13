# Legacy Yii Redis AR refresh continuity v1

This is a feature-off compatibility protocol, not evidence that a live Redis
database is ready, drained, exported, or supported by the current Identity
importer. A1 keeps its reusable permanent predecessor. The server consumer keeps
its delete-on-consume predecessor. Policy belongs to the endpoint, never to a
guessed issuer, token length, or old row shape.

## Shared engine and adapter contract

`api/components/LegacyArRefreshContinuity.php` has namespace `app\components`, no
Yii/model dependency, and may be copied byte-for-byte into the PHP API. Its Redis
callback is `fn(string $command, array $args): mixed`, matching Yii Redis
`executeCommand`. Each public operation requires its adapter to hold the current
SQL account row `FOR UPDATE` until the operation returns. Account input is
`{id:int,status:int,passwordHash:?string,authKey:?string,accountEpoch:string}`; status must be 10.

* `__construct(callable $execute, string $binaryKey32, string $consumer,
  ?callable $verifyProvenance = null)`: consumer is exactly `a1` or `server`.
* `credentialUserId(string $raw): int`: metadata lookup only, used to select the
  SQL lock; this does not authenticate the credential.
* `issue(array $account, callable $buildResponse, array $recordFields=[]): array`:
  creates a new family and credential. Builder receives `(string $raw,
  array $provenance, int $redisTimeMs)` and returns the complete response array.
* `refresh(string $raw, array $account, string $endpoint,
  ?string $idempotencyKey, callable $buildResponse,
  array $recordFields=[]): array`: complete-response replay or atomic successor
  issuance. `endpoint` is a trusted code constant, not a request parameter.
* `revoke(string $raw, array $account): void`: revokes the complete family,
  including predecessors and all successors, even a consumed predecessor.
* `revokeUser(array $account): void`: advances a persistent user epoch. Invoke
  under the same SQL row lock before password/auth-key/status/delete mutations.
* `registerReviewedCredential(string $raw, array $account,
  array $review): void`: offline, explicit complete-row CAS backfill only. Review
  contains exact `recordId`, `record` (all Redis hash fields), `recordKeyEncoding`
  (`plain|sha256`), `expiresAtMs` (null or exact absolute time), and a 64-hex
  `reviewSha256`. It never guesses a credential's provenance or expiry, and is
  not called automatically by refresh.
* `registerReviewedRecordHash(string $hash, array $account,
  array $review): void`: offline complete-row CAS for a legacy SHA256-keyed
  record whose original bearer is unavailable. Requires lowercase SHA256,
  `recordKeyEncoding=sha256` and the exact same `record.key`. It performs the
  same owner, epoch, fingerprint, expiry and provenance checks; a plaintext
  record still requires `registerReviewedCredential` with its actual raw key.

`recordFields` is an allowlisted AR successor template. A1 leaves it empty and
stores a plaintext key with no expiry. Server supplies `keyEncoding=sha256`,
`expiresAtMs` and the AR fields `session_id,user_agent,ip,created_at,expires_at`,
plus paired `auth_method=device_sn,device_sn_id` if applicable. Expiry values
must agree exactly. Builder failures cause no Redis writes. Redis writes are a
single Lua CAS; a lost acknowledgement never triggers an automatic second EVAL.
The injected Redis executor must not retry commands. The A1 adapter refuses a
Yii Redis connection with `retries` other than the integer `0` before creating
the engine; this avoids a client silently re-executing an ambiguous EVAL.

## Keys, types and persistence

The fixed prefix is `identity:legacy-refresh:ar:v1:`. Actual Redis DB and SQL
account namespace must be independently reviewed as shared by both consumers.
All numbers below are canonical decimal strings. Null is the empty string.
All metadata, family membership and user epochs persist without TTL. Only
response caches expire. This does not change any real credential expiry. This AR protocol accepts the
known non-TTL AR hashes/counter/list only; unexplained physical TTL is refused,
not removed or extended.

* `ready` HASH: exact `v=1,protocol=legacy-ar-refresh-continuity-v1,
  consumers=a1,server,reviewSha256=<64 lowercase hex>,state=ready`. This marker
  is installed only by a separately reviewed offline rollout/backfill process
  after all issuers, logout and security writers use this protocol. Runtime
  never creates it. Unknown metadata is 503, not an implicit logout or backfill. Offline backfill
  may operate with state=preparing; online operations require state=ready.
* `user:<userId>:epoch` STRING: positive integer, never reset or expired.
* `byHash:<sha256(raw)>` HASH: exact
  `v,hash,recordId,userId,familyId,epoch,accountEpoch,fingerprint,expiresAtMs,
  recordKeyEncoding,recordSha256,predecessorHash,authMethod,deviceSnId,
  consumedBy,consumedAtMs,serverSuccessorHash,reviewSha256`.
  `familyId` is 32 lowercase hex. `consumedBy` is empty or `server`.
  `authMethod/deviceSnId` are both empty or `device_sn` and a positive integer.
  Provenance requires a live adapter verification on every issue/replay;
  unsupported provenance fails closed rather than becoming an ordinary JWT.
* `family:<familyId>` HASH: exact
  `v,userId,epoch,accountEpoch,fingerprint,revoked,revokedAtMs`; revoked is `0|1`.
* `family:<familyId>:members` SET: credential SHA256 hashes. A1 can create
  several successors for one P. Only server consumption has one server edge.
* `cache:<consumer>:<sha256(endpoint)>:<Psha>:<requestHash|none>` HASH: exact
  `v,responseVersion,consumer,endpoint,issuer,predecessorHash,successorHash,
  userId,familyId,epoch,accountEpoch,fingerprint,requestHash,createdAtMs,expiresAtMs,
  encryptedResponse`. `issuer` is the complete response's HTTP issuer supplied
  by the trusted adapter in recordFields (required for refresh).

Fingerprint is SHA256 of UTF-8 JSON with no whitespace and unescaped Unicode, line terminators or
slashes: `["yii-ar-account-v1",password_hash|null,auth_key|null]`. It matches
Identity's existing `legacyRefreshAccountFingerprint`. Current status and any
source device authorization are checked live. `accountEpoch` comes from
`identity_legacy_security_versions(user_id INT signed PRIMARY KEY, epoch BIGINT
UNSIGNED NOT NULL, updated_at DATETIME(3))`, read in the same SQL transaction
after locking the user. It is a canonical decimal string in 1..18446744073709551615, never cast to
float or a JavaScript number. It is mandatory and missing rows fail 503. Reviewed
AFTER INSERT/UPDATE/DELETE user triggers advance the persistent SQL epoch on
password/auth_key/status changes (null-safe byte-exact comparison, regardless of
the legacy column collation) or deletion; backfill
never lowers it. Runtime never creates this ledger or triggers. This covers
static/vendor/raw SQL writers and account ABA. Redis epoch separately records
explicit user-wide session revocation. Epoch changes prevent a disabled
then re-enabled account, or credentials changed back, from reviving old sessions.

## Atomic refresh and replay

Idempotency-Key is absent or a nonempty 1..256 byte string without control
characters. Present keys use SHA256 of the exact bytes and a 30,000ms replay
window; absent keys use `none` and 5,000ms. The absolute cutoff comes from Redis
TIME at issuance, is bounded by finite P/S expiry, and is never extended on read.
Complete JSON responses are bounded to 256KiB.

AES-256-GCM uses an explicitly configured stable 32-byte consumer key, never a
JWT key derivation or generated startup key. Encoding is base64 of
`nonce12 || tag16 || ciphertext`. AAD is compact JSON of this ordered array:
`["legacy-ar-refresh-response-v1",consumer,endpoint,issuer,predecessorHash,
successorHash,userId,familyId,epoch,accountEpoch,fingerprint,requestHash,createdAtMs,
expiresAtMs]`, with all field values strings. The responseVersion is exactly
`legacy-ar-refresh-response-v1`. Consumers do not decrypt each other's caches.

Lua validates key types and exact metadata/family/epoch/complete AR snapshots
before its first write. It checks expiration and revocation again at Redis TIME.
It atomically stores successor AR hash/list/counter, metadata and encrypted whole
response. The SQL account lock serializes account/security changes; the Lua CAS
also protects consumers using the same Redis.

For A1, raw-key lookup is preserved: a sha256-keyed server credential is not
accepted by this endpoint. Both issuance and cached replay require the original P AR hash to still
exist, match its reviewed owner/key/expiry/provenance and be active. P is never
deleted, expired, shortened or overwritten. A missing/expired/revoked S blocks
that cached replay but does not delete P or prevent a later fresh A1 redemption.

For server, new issuance atomically deletes P's AR hash/list member and writes a
`consumedBy=server` tombstone plus the exact successor edge. Missing P allows
only the matching server endpoint/request cache while S and family remain live.
It never permits a new issuance. A1 rejects P after server consumption even if
its own encrypted response remains cached. Any P/S logout revokes the family.
User revocation advances epoch. AR revocation and expiry always beat replay.

Redis errors, ambiguous EVAL results, malformed metadata or missing readiness
fail closed without local/other-consumer fallback or a retry. Lua prechecks all
key types and counter bounds to avoid ordinary partial-write script errors.
Redis persistence/durability and complete writer rollout remain deployment gates.

## A1 feature rollout

`AR_REFRESH_CONTINUITY_ENABLED=false` is the default. Enabling requires an
explicit base64 `AR_REFRESH_REPLAY_KEY_BASE64` that decodes to exactly 32 bytes,
the reviewed ready marker/backfill and all shared credential/security writers
upgraded. Never put an actual key in a command, document or chat. Identity mode
uses the existing Identity bridge and does not run this legacy engine.

A1 refresh caches its existing `{success,message,nickname,token}` body. Common
report caches its complete `{success,message,user:{nickname,token},data}` body
and issues once when enabled. With the feature off, the legacy response paths
remain unchanged. Login/key redemption/User::token new credentials receive
shared family metadata. Reusable P never acquires a new artificial 30-day TTL.

All A1 instances of the same consumer must use the same explicitly managed key.
The ready review must verify the actual Redis DB/prefix **and** SQL account
namespace of every shared writer. Sharing Redis alone does not prove that equal
integer user IDs in different SQL databases identify the same account. Unknown
writers, incomplete old-row backfill, missing SQL ledger/triggers or an
unreviewed namespace block enablement. No operational ready marker, real key or
backfill has been produced by these local tests.

CI provisions disposable MySQL 8.4 and Redis 7.4 for the original Identity
boundary suite. Local equivalent uses the explicit `A1_CONTINUITY_MYSQL_TEST=1`
and `A1_CONTINUITY_REDIS_TEST=1` flags plus strictly local fixture addresses;
run `php vendor/bin/phpunit -c tests/identity-authority.phpunit.xml` in `api`.
Production environment variables are never used by those test fixtures.

## Future cutover export/import gate

A future private, reviewed snapshot must have kind/version
`legacy-ar-refresh-continuity-v1`/1, actual Redis DB/prefix/account source proof,
complete AR rows, metadata/family/epoch and revocation/tombstone graph, per-
consumer/endpoint original encrypted complete responses, issuer, provenance and
exact original absolute cutoffs. It must prove all writers drained; an operator
statement alone is not drain evidence. No expiry/issuedAt may be invented for
permanent A1 AR rows. Export must not expose keys/responses in stdout.

The current Identity legacy AR ledger importer does **not** accept this graph or
old full JWT response replay. A new strict importer/response version and tested
cross-cutover replay are required before claiming this part of cutover ready.

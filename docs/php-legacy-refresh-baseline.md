# PHP legacy refresh baseline

This is the temporary compatibility writer used before Identity cutover. The
shared protocol is specified in `legacy-refresh-continuity.md`; the engine is
byte-identical to A1's `api/components/LegacyArRefreshContinuity.php`.

`AR_REFRESH_CONTINUITY_ENABLED` defaults to false. Enabling requires the reviewed
SQL security ledger/triggers, complete old AR metadata backfill, a ready marker,
all shared writers upgraded, Redis retries set to integer zero, and an explicitly
managed canonical base64 32-byte `AR_REFRESH_REPLAY_KEY_BASE64`. Never generate or
derive a deployment key in this adapter. Independent Identity ownership rejects
legacy writer enablement.

PHP consumes the predecessor AR record and returns the original successor on
retry: 30 seconds for the same opaque `Idempotency-Key`, five seconds without a
key. Neither cache reads nor retries extend those original absolute deadlines.
The encrypted cache contains the complete original `/v1/auth/refresh` body,
including the original JWT. A consumed, revoked, malformed or unprepared known
AR credential cannot fall through to QR redemption, including a raw credential
whose text starts with `web_`.

The adapter holds the current SQL account and ledger locks through the single
Redis compare-and-set commit. CynosDB's automatic SQL retry is suppressed for
the entire lock/commit interval and restored on every exit. Redis mutations are
never retried internally after an ambiguous acknowledgement. Caller-owned SQL
transactions stay owned by the caller.

SN provenance comes from the stored predecessor. Issue and replay recheck the
live SN binding, enablement and ordinary-user role. Public request fields cannot
remove that provenance. Logout of either predecessor or successor revokes its
whole family; account credential/status changes advance a durable SQL version,
so changing a value back cannot revive old credentials or caches.

Validation includes unit coverage of default-off/invalid configuration,
independent-mode writer refusal, stable-key and Redis retry validation, QR/AR
collision handling, and a disposable real MySQL/Redis adapter suite:

```sh
AR_CONTINUITY_MYSQL_TEST_HOST=127.0.0.1 \
AR_CONTINUITY_MYSQL_TEST_PORT=3306 \
AR_CONTINUITY_MYSQL_TEST_PASSWORD=root \
AR_CONTINUITY_REDIS_TEST_HOST=127.0.0.1 \
AR_CONTINUITY_REDIS_TEST_PORT=6379 \
php tests/integration/legacy_ar_continuity_mysql.php
```

Run from `advanced/` against disposable local fixtures. The suite creates a
random database, uses Redis DB14, and removes its fixture data in `finally`.
It refuses nonlocal hosts. Do not point it at an application database or Redis.
CI makes this real integration suite mandatory before publishing a candidate.
It checks cross-consumer PHP/A1 behavior, a competing SQL writer during Redis
commit, lost acknowledgement recovery, transaction ownership, byte-level ABA,
unsigned 64-bit versions, and live SN disable/role/unbinding checks.

Passing these tests does not import cloud credentials, configure keys, prove a
writer drain, preserve PHP session files, deploy a candidate, or activate native
Identity. Those remain explicit cutover gates in the superproject deployment
manifest.

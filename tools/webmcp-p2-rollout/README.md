# WebMCP P2 temporary migration bundle

Source: `495158c6` in `/private/tmp/xrugc-p2-release-server-20260913`.
Fixed application root: `/var/www/html/advanced`. Extract under container `/tmp`.
The three production PHP source files are exact copies. `manifest.json` verifies their hashes and the runner before boot.

The runner loads the live common and console configuration, preserving database credentials and target. It uses standard Yii Connection/Command without SQL retries, disables replicas and schema caching, sets this maintenance connection to utf8mb4 and a 10 second connection timeout, and registers only the P2 controller. It does not replace live code or alter existing scene/archive data.

For each distinct server/database target, run the following. Substitute `bujiaban_development` when working with that configured target; changing the expected argument never changes the configured database.

```sh
cd /tmp/xrugc-p2-migration-bundle-20260913
php maintenance.php bujiaban preflight
php maintenance.php bujiaban backup
php maintenance.php bujiaban export
php maintenance.php bujiaban plan
php maintenance.php bujiaban up EXACT_SHA256_FROM_BACKUP_OUTPUT
php maintenance.php bujiaban verify
```

Preflight and backup use a REPEATABLE READ, READ ONLY transaction. Preflight exposes only DB identity fingerprint/name/version, configured original charset, maintenance session charsets, 15 required engines, max packet, migration versions, archive counts and aggregate current-snapshot byte statistics. It omits row bodies and SQL/schema dumps. A missing P2 table is expected before migration; any other missing/non-InnoDB participant, missing P1 migration/RBAC parents, or packet size under 17 MiB blocks `up`.

`backup` writes a mode-0600 `before-DATABASE.json` exclusively and refuses overwrite. Its scope is affected RBAC rows, P1/P2 migration records, relevant schemas and aggregate metadata. It does not back up scene/resource/archive content because P2 is additive and does not modify those old rows. `export` outputs only the gzip/base64 backup for operator retention. Backups must also be retained outside the container before Watchtower replaces it.

`up` requires the exact backup SHA256 and rechecks database name/server fingerprint and readiness before applying only `m260913_120000_add_scene_publication_history`. Both `up` and `verify` verify schema/indexes/storage/RBAC and preservation of pre-existing affected RBAC/migration rows. It never bulk-runs unrelated migrations or offers down/delete operations.

New production PHP requests must use utf8mb4. The source commit fixes common DB configuration; if deployed runtime config is mounted independently, verify/update that actual configuration before enabling P2 traffic. The maintenance session override alone does not fix business traffic.

After both databases on both deployment hosts are migrated and verified, branch/image publishing may proceed. Watchtower scans every 180 seconds; develop consumers can point at production data, so no image tag should be pushed before all required databases are ready.

Local validation: PHP syntax checks; 38 WebMCP unit tests/123 assertions; separate SQLite fixture exercises runner read-only captures/byte aggregation, exact plan/up/verify, unchanged old RBAC, idempotent up and readiness failures. The SQLite fixture substitutes MySQL metadata queries and does not establish MySQL engine/charset behavior; real MySQL gates are run separately by the release coordinator.

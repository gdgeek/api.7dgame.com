# Task 5.1 Stage B coordinator API

This control plane is deployable but disabled unless
`TASK51_STAGE_B_COORDINATOR_ENABLED` is explicitly true and
`TASK51_STAGE_B_COORDINATOR_PUBLIC_ORIGIN` is exactly
`https://api.xrteeth.com`,
`TASK51_STAGE_B_COORDINATOR_SERVER_PUBLISH_SHA` is an exact lowercase 40-hex
publish identity that must also match the image's build-time
`advanced/GIT_COMMIT`; a missing, malformed, or mismatched embedded commit
keeps the coordinator unavailable. The dedicated `TASK51_STAGE_B_INTERNAL_TOKEN` is an
RFC 4648-canonical, unpadded 256-bit base64url secret (including zero pad
bits).
There is no fallback token; incomplete deployment configuration makes every
endpoint return 404. The public claim request does not transmit the internal
token.

Every action also requires the raw public `Host` to be exactly
`api.xrteeth.com`. Ports, aliases, case variants, trailing dots, and
`api.tmrpp.com` fail closed with 404. Before enabling, Stage A must prove the
edge preserves this Host, terminates HTTPS, strips and rebuilds forwarded
headers, routes only the canonical vhost to the coordinator replicas, and that
all such replicas use the same MySQL authority. Until exact trusted-proxy CIDRs
are configured in Yii, the application does not infer HTTPS from untrusted
forwarded headers.

The API entrypoint defaults to `YII_DEBUG=false` and `YII_ENV=prod`. If the
Task 5.1 feature flag is present in a debug or non-production runtime it exits
before bootstrapping Yii, so debug RequestPanel cannot read or persist the
capability, internal token, B, or E. The controller repeats the production
runtime check and rejects any registered debug/gii module. Local compose
explicitly opts back into dev/debug while leaving Task 5.1 disabled. Stage A
must attest the deployed entrypoint, actual container runtime, and active
module list rather than relying on repository defaults before any real
capability is generated or sent.

All requests use raw, byte-for-byte canonical JSON as the body. Canonical JSON
has recursively ASCII-sorted object keys, no insignificant whitespace, ASCII
bytes only, and exactly one trailing LF. Responses containing receipts use the
same format and `Cache-Control: no-store, private, max-age=0`.

## Endpoints

- `POST /v1/task51/stage-b/issue` is internal-only. Headers:
  `X-Task51-Internal-Token` and `X-Task51-Claim-Capability`. The body is raw
  canonical Stage B v3. B authorizes the exact current-Production direct
  matrix schema/ref, 256 cells, and non-zero four-role subject aggregate. The
  independent matrix parser later proves that aggregate was canonically
  computed from four non-zero, pairwise-distinct role subject digests and that
  the same role identities bind the 56-cell ledger and both nodes' business
  sessions. The capability is
  a separately generated 256-bit
  canonical unpadded base64url secret. `claimCapabilitySha256` is SHA-256 over the exact
  43 ASCII base64url bytes; only this lowercase hash is present in B or the
  database.
- `POST /v1/task51/stage-b/claim` is called by the controlled Playwright
  supervisor, never by page or worker JavaScript. It requires exact
  `Origin: https://d.xrugc.com`, `X-Task51-Claim-Capability`, and raw canonical
  Stage B v3 as its body. Success is the canonical
  `wp3-task51-stage-b-global-claim-receipt-v1` safe receipt. The receipt never
  contains the capability or its hash. `Origin` is a protocol/deployment
  binding, not authentication: non-browser clients can set it, so possession
  of the one-shot capability remains the authorization boundary. The global
  browser CORS policy intentionally does not allow the capability header.
- `POST /v1/task51/stage-b/consume` is internal-only. It requires
  `X-Task51-Internal-Token`, `X-Task51-Runner-Export-Receipt-Ref`, and raw
  canonical runner export receipt E as its body. Success is canonical C,
  `wp3-task51-stage-b-global-consumption-receipt-v1`. E uses
  `wp3-task51-stage-b-runner-export-receipt-v3`. It binds the canonical safe
  browser-network receipt N, runner fragment F
  (`wp3-task51-runner-fragment-v3`), runner result R
  (`wp3-task51-stage-b-runner-result-v3`), and current-Production direct
  matrix (`wp3-task51-production-direct-matrix-v1`) by exact JSON evidence
  refs and SHA-256 values. Matrix refs are JSON-only `reports/...` paths;
  empty, `.` and `..` path segments fail closed.

The MySQL/InnoDB row is the only authority. State moves only
`ISSUED -> CLAIMED -> CONSUMED`; `CLAIMED` is never released. A second claim
conflicts, including a replay with the same capability. This preserves B's
exactly-one authorized claim POST; an uncertain claim response burns the
execution until a separately approved read-only recovery protocol exists.
Re-consuming the exact same E and evidence ref returns the stored C;
a different E or ref conflicts. State, counts, canonical receipts, hashes, and
the append-only transition row are committed in one transaction using the
database clock. The authoritative row also stores B's pre-locked Production
matrix evidence ref and subject digest. `consume` compares both fields with E
before the consume CAS, so a drifted E is rejected while the row remains
`CLAIMED`; it cannot burn the one-shot execution into a false C.

C deliberately stores and returns the exact E hash together with the exact B
hash; E in turn carries the F, R, N, and matrix ref/hash bindings. The
coordinator immediately enforces the B→E matrix ref/subject binding, while the
independent evidence validator proves the raw matrix, F, R, N, E and complete
B→F→R→E→C chain before C can be used in a closeout. The coordinator does not
ingest raw F, R, N, or matrix bytes; adding such ingestion would be a separate
API contract rather than an implicit change to `consume`.

The transition table is append-only at the database layer: MySQL triggers
reject every `UPDATE` and `DELETE`, while the unique ordinal, shape checks, and
foreign key constrain inserts. Migration and integration sessions set a
20-second metadata-lock timeout. The dedicated migration controller sets that
timeout on its exact connection before inspecting migration history or Task
5.1 tables, and the migration repeats it before DDL. Dedicated Task 5.1
connections also use a 5-second PDO connection timeout, so an uncooperative
session or unreachable authority cannot leave an approved window waiting
indefinitely.

The coordinator uses the lazy `task51CoordinatorDb` component, an ordinary Yii
`Connection` with the ordinary `Command`, against the same configured MySQL
authority. It deliberately does not use the application's `CynosDbCommand`,
whose per-statement transparent retries would make a transaction outcome
ambiguous after a connection failure. This dedicated component does not alter
the existing application database component or its retry policy.

`task51CoordinatorDb` must have an empty `tablePrefix`. One database carries
exactly one unprefixed Task 5.1 authority; multiple prefixed copies in one
schema are unsupported and fail before the first migration DDL.

The migration enforces the same adapter boundary before its first DDL. The
dedicated controller filters the plan to the one exact Task 5.1 migration and
pins both its path and database component. From `advanced/`, first require the
machine-readable plan to report `TASK51_MIGRATION_PLAN=EXACT_ONE`, then apply
exactly one migration:

```bash
php yii task51-migrate/plan --db=task51CoordinatorDb
php yii task51-migrate/up 1 --db=task51CoordinatorDb --interactive=0
```

`plan` 必须只输出一个 `TASK51_MIGRATION_PLAN=EXACT_ONE:<migration>` 或
`TASK51_MIGRATION_PLAN=ALREADY_APPLIED` 机器状态；已应用分支还会输出
`TASK51_MIGRATION_SCHEMA_SHA256=<64hex>`。`up` 必须只输出一个
`TASK51_MIGRATION_APPLY=APPLIED_EXACT_ONE` 或
`TASK51_MIGRATION_APPLY=ALREADY_APPLIED_NOOP`，并始终输出同一份完整
`TASK51_MIGRATION_SCHEMA_SHA256=<64hex>`。任何缺失、重复或指纹变化都必须停止。

Do not use the general `yii migrate` command for this window: it can include
unrelated pending migrations. The dedicated command rejects a path override,
any limit other than one, a subclassed/Cynos connection, a non-standard
command class, or slave routing before creating a Task 5.1 table.

The migration is deliberately irreversible: generic migration rollback never
drops the execution or transition ledger, even while both tables are empty.
Operational cleanup disables the routes and preserves this authority; deleting
it requires a separate evidence archive and explicitly destructive procedure.
MySQL DDL is implicitly committing, so Stage A must exercise the migration on
an isolated clone and document recovery of any partially created schema. The
opt-in integration suite owns a guarded `task51_test_*` database and performs
its destructive table teardown explicitly; it does not weaken production
`safeDown()`.

The application does not log raw B, raw E, capabilities, internal tokens,
control headers, or request bodies. Operators must apply equivalent redaction
at any reverse proxy or edge layer before enabling this endpoint. The codec's
16 KiB limit is applied after PHP receives the body, so the edge and PHP runtime
must also enforce a request-body limit, connection limits, and an
endpoint-specific rate limit before the public claim route is enabled.

## Opt-in MySQL proof suite

`advanced/tests/integration/Task51StageBMySqlCasTest.php` is destructive and is
not part of the default PHPUnit suite. It has no fallback to the application
database. It runs only with `TASK51_MYSQL_INTEGRATION=1`, explicit
`TASK51_MYSQL_TEST_DSN`, `TASK51_MYSQL_TEST_USER`, and
`TASK51_MYSQL_TEST_PASSWORD`, and then refuses any selected database whose name
does not begin with `task51_test_`. Run that file explicitly against a clean,
disposable Oracle MySQL 8.0.19+ database. The two-process CAS and lock-wait
cases also require the CLI `pcntl`/`posix` extensions. The test account needs
`CREATE`, `ALTER`, `DROP`, `TRIGGER`, and access to
`performance_schema.data_lock_waits`/`threads`; a schema-scoped MySQL named
lock prevents two suite processes from deleting each other's tables. From
`advanced/`, invoke the file explicitly, for example
`vendor/bin/phpunit tests/integration/Task51StageBMySqlCasTest.php`.

The Release image installs `php.ini-production` plus an explicit security
override that keeps `display_errors` and `display_startup_errors` off, enables
`log_errors`, and enables `zend.exception_ignore_args`. Task 5.1 raw B/E and
capability parameters are additionally marked `SensitiveParameter`; both
layers must remain present in the release contract.

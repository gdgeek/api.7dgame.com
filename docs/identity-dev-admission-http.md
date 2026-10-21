# Development HTTP admission adapter (default OFF)

This is the framework integration for the existing private `driver/identity-dev-admission` protocol. It does not activate Identity, close a gate, change a writer, install a gateway, or certify the complete writer roster. There is no public control route and no key generator.

## Source and request lifecycle

The PHP source entries are `files/api/web/index.php` and `files/backend/web/index.php`. Deployment must install those as the actual `advanced/{api,backend}/web/index.php`, together with `advanced/common/components/admission/`. The API's pre-existing Task51 early rejection checks remain ahead of admission; they do not initialize authentication, sessions, or source writes. Admission then runs before Composer, Yii, configuration, bootstrap, authentication and session initialization.

A1 has the same engine under `api/components/admission/`, with namespace `app`. Its source and deployed entry `files/api/web/index.php` and `api/web/index.php` agree. Its Dockerfile copies `api/`, then the `files/api/web/index.php` override. The engine files must travel with that image. The adapter has no database or schema change.

With the feature unset, empty, `0`, `false`, or `off` (case-insensitive, trimmed), the original Yii application path runs without loading the adapter or reading private configuration. Enabled values are `1`, `true`, `yes`, or `on`; any other non-disabled value rejects configuration before the application runs.

When enabled, the adapter registers the exact operator-reviewed boot identity. A gateway-provided `X-Identity-Admission-Lease` is trusted only after the coordinator successfully enters it. A direct request obtains a root lease first. Malformed, forged, duplicate or refused input cannot initialize Yii/session or create an untracked handler. Before Yii starts, the private enter ACK must contain exactly canonical operationId/rootOperationId/positive epoch, and match the returned capability metadata and this instance. Decoding capability metadata never grants authority; the successful private enter is still mandatory. Every subsequent validate must match that saved triple. A continuation ACK must bind the returned child capability and target, preserve the parent rootOperationId and epoch, and allocate a different operationId; a self-consistent child ACK cannot renew or substitute its parent scope. Register/complete/status ACKs require the exact own instance row and valid counters; unknown/lost require exactly accepted:true. Unexpected409 journal errors,5xx or malformed ACKs remain unknown, not ordinary public refusal. Other public control-like headers are never used as trusted identity or credentials. The gateway must still strip all public internal markers and inject only its verified lease.

The handler runs with `ignore_user_abort(true)`. Yii `end()` sends its original response/events, then throws an owned termination exception to unwind actual business `finally` blocks. A swallowed or repeated termination, raw `exit`, uncaught exception, fatal error, active nonstandard error handler, observed session-write failure or annotated work error becomes permanently unknown. Exceptions are not automatically retried.

Completion is after the handler has actually unwound, synchronous work, real PHP session writes, Yii session bookkeeping, actual logger export, known Yii shutdown callbacks and a final check for active instantiated Yii DB transactions. A successful response, `afterRequest` or a client disconnect is never the completion event. An already finished operation with a lost completion ACK becomes an unknown instance; no repeat completion or inferred zero is allowed.

Before parent completion, bounded read-only `validate` polling waits for actual coordinator `childrenActive === 0` and `unknown === false`. A child HTTP response alone does not satisfy that condition. The default wait is 1500 ms, 25 ms between reads, capped by the original configured request budget. An overdue pending child makes the parent unknown without forgetting the child. New continuation issuance and its ACK both consume the original fixed arrival budget; an expired or late ACK never returns a forwarding header or dispatches a callee. Initial register/begin/enter also consume the same fixed budget. A completed long handler that issued no child may still perform one bounded validation (25ms after expiry) and one bounded completion to record actual work; ordinary business duration alone does not fabricate an unknown transaction. Unknown ACKs are never polled as if they were known pending children.

## Explicit private configuration

All configuration is trusted process environment; do not accept it from request parameters, headers or browser storage. No real key belongs in source, commands, logs or this document.

| Variable | Contract when enabled |
| --- | --- |
| `IDENTITY_DEV_ADMISSION_ENABLED` | Explicit enabled value; defaults OFF |
| `IDENTITY_DEV_ADMISSION_ENVIRONMENT` | Exactly `xrugc-development` |
| `IDENTITY_DEV_ADMISSION_CONTROL_URL` | Exactly `http://127.0.0.1:<1..65535>/`; same-host sidecar/fixture only |
| `IDENTITY_DEV_ADMISSION_KEY` | Operator-supplied per-boot key; canonical unpadded base64url, 32..64 bytes; never generated here |
| `IDENTITY_DEV_ADMISSION_INSTANCE_ID` | Exact reviewed roster ID, lowercase identifier up to64 chars |
| `IDENTITY_DEV_ADMISSION_BOOT_ID` | Exact reviewed boot identifier,8..80 URL-safe chars |
| `IDENTITY_DEV_ADMISSION_REVISION` | Exact40 lowercase hex source revision |
| `IDENTITY_DEV_ADMISSION_ROUTER_HASH` | Exact64 lowercase hex reviewed router hash |
| `IDENTITY_DEV_ADMISSION_GENERATION` | Exact roster generation `legacy` or `identity` |
| `IDENTITY_DEV_ADMISSION_CHILDREN` | Comma-separated unique reviewed child IDs, no whitespace; empty means none |
| `IDENTITY_DEV_ADMISSION_CHILD_WAIT_MS` | Optional canonical integer25..5000, default1500; actual remaining request budget wins |
| `IDENTITY_DEV_ADMISSION_REQUEST_BUDGET_MS` | Optional canonical integer25..5000, default1500; must match measured original request budget |

The enabled runner requires PHP exception arguments to be hidden before it reads the private key, and removes the key from `$_SERVER`/`$_ENV` before Yii debug handlers initialize. The trusted process environment is still the key source for each request; this patch does not change or generate that key. Disabled behavior does not change these PHP settings or superglobals.

Private RPC uses the existing register/begin/enter/continue/validate/complete/unknown/lost protocol. Enabled mode requires the PHP cURL extension; there is no weaker stream fallback. Requests and responses have strict size bounds, no redirects, no ambient HTTP proxy, sanitized errors and no automatic retry. Each RPC has a maximum total wall clock of2000ms covering connection, headers, body and codec, enforced by libcurl total timeout plus the original monotonic deadline. Slowly progressing headers/body cannot renew an idle timeout. Admission, continuation and child-validation RPCs are further capped by the remaining original budget. Unknown/lost reporting uses at most one attempt per action/capability, each at most100ms, sharing a separate250ms safety tail; this tail is not claimed to be inside the user request budget and must be included in the real gateway cutover measurements. This is not a claim that every existing business handler meets the configured request deadline. The gateway/coordinator must cancel before activation when measured end-to-end queue, drain, synchronization, activation and forwarding cannot fit the ordinary request budget.

`HttpAdmission::work(callable)` is an explicit synchronous side-effect/compensation boundary. Exceptions latch unknown even if the caller catches them later. Disabled behavior calls the callback normally. `continuationHeaders(targetId)` obtains an actual tracked child capability and returns only its lease header; disabled behavior returns no headers. The receiver must enter and complete the lease under its own exact reviewed boot identity. This patch exposes that API and exercises it with a real HTTP child fixture; it does not wire all existing HTTP clients.

## Supported lifecycle and outstanding barriers

The enabled profile currently supports `apache2handler` and PHP's local `cli-server`, no session.auto_start, and Yii's standard `yii\web\Session` configuration (the observed subclass is installed before application bootstrap). Arbitrary custom Yii Session and Yii ErrorHandler components are rejected before bootstrap; the enabled profile uses the reviewed standard Yii ErrorHandler. Session-handler write callbacks are still actually observed. FPM/FastCGI/CLI are unsupported and cannot certify completion. In particular, no `fastcgi_finish_request()` behavior is claimed.

Source scans found no application-owned shutdown callbacks/error-handler replacement in the current PHP/A1 HTTP business sources; Yii's actual logger and session shutdown handlers were read and are exercised. This is not a proof for future callbacks, arbitrary destructors, uninstantiated secondary DB connections or hidden background work. Any newly added late callback must be reviewed/instrumented before its writer joins the drain roster. A caught DB/Redis/HTTP commit-unknown hidden inside unannotated application code cannot be inferred from an empty transaction list; those concrete boundaries still need `work`/unknown integration.

Still required before any online switch:

- Wire and review actual inter-service continuations, all subcalls and compensation paths; synchronous completion or HTTP200 does not prove a remote write has finished.
- Inventory and integrate CLI, cron, queue workers, maintenance/admin scripts, direct SQL/Redis writers and any alternate HTTP entry. This adapter does not gate those paths.
- Review each actual image/entry/config and same-host sidecar installation, immutable boot roster/router mapping, and process replacement/loss. Loopback in a Docker container does not reach another container or host. Cross-host B0/B1 requires a separately reviewed private TLS/control topology; no such connectivity is claimed here.
- Measure real ordinary request deadlines and all drain/final-sync/activation durations. Do not expose maintenance pages or treat502/503 from stopping containers as continuity. No source activation when the budget cannot be met; post-commit unknown never releases traffic back to the old writer.
- Obtain actual sealed coordinator journal, epoch and complete roster evidence. `writers_drained=true`, source-code tests or this framework wrapper cannot substitute for that evidence.

## Independent local and CI verification

The test harness uses disposable synthetic fixtures with real Yii2, actual PHP sessions, SQLite transactions, Node child handlers and both PHP's builtin server and Apache/mod_php. PHP and A1 each carry the same harness and test only their own source and Composer-locked vendor tree. There is no dependency on another application checkout or a cached/private application image. `fixture-config.mjs` requires an explicit own-repository framework and compares the installed Yii version/source reference to that repository's actual composer.lock. Missing Composer dependencies fail the gate.

The `protocol/` directory contains byte-frozen production coordinator, journal, participant client, gateway and hold queue from superproject commit `f603930906d55c54fc2c3552ed2f431d05cafe18`, plus its separately identified `test-support.mjs` helper. `manifest.json` records their exact SHA256 values and upstream directory. Tests always execute these production bytes. If a local superproject driver is found, or `ADMISSION_PROTOCOL_DIR` is explicitly supplied for a local check, every byte must match the frozen snapshot before import. Independent CI needs no superproject checkout. Snapshot provenance, closed file list, drift, symlink and missing-source failures are tested; the production core is not replaced by a mock.

A1 was freshly cloned from pushed4796b488aa21053d7d45d2a8f68ae3056ba37d97 into the persistent isolated checkout on2026-10-21; the original7-file staged patch was reconstructed byte-for-byte to077a0b790e38e6ce6f1f7440e6dfbd8a12e3c247fb16c141cd60850fb3175ab7 before the new fixes. Old temporary files/index and old62-test evidence are not current validation. The final local A1 independent run reused a reviewed dependency cache after its source composer.lock was byte-identical (SHA256 `a312b8ce527cf339c7fc1cc591f43c753c7324be90cab3931c35e7dd7e9910f7`) and every production package version/source reference matched the own lock. This is local dependency-cache provenance, not a private test runtime or a harness fallback. A fresh local Composer download encountered a GitHub TLS EOF for Bootstrap; TLS validation was retained. Fresh install remains mandatory in the actual GitHub test job and has not been claimed successful locally.

The fixture Dockerfile builds only from public multi-platform `php:8.5-apache-bookworm` and `node:24-bookworm` version tags. These test-only base tags may receive patch updates; no unverified local image digest is presented as a public CI pin. It asserts cURL, mbstring and PDO SQLite are present; no application source, vendor cache, business configuration, data or credential is baked in. Install each repository's normal Composer lock dependencies before running. No business database, Redis, real user or stable credential is used. Random control keys exist only in fixture RAM. Tests execute with `--network none` and a read-only source/vendor mount.

From the PHP repository root, with its normal `advanced/vendor` installed:

```sh
docker build -t identity-admission-php-ci advanced/tests/manual/identity-dev-admission
docker run --rm --network none \
  -e ADMISSION_FIXTURE_FRAMEWORK=server \
  -v "$PWD:/workspace:ro" \
  identity-admission-php-ci \
  /workspace/advanced/tests/manual/identity-dev-admission/run-ci.mjs
```

From the A1 repository root, with its normal `api/vendor` installed:

```sh
docker build -t identity-admission-a1-ci api/tests/manual/identity-dev-admission
docker run --rm --network none \
  -e ADMISSION_FIXTURE_FRAMEWORK=a1 \
  -v "$PWD:/workspace:ro" \
  identity-admission-a1-ci \
  /workspace/api/tests/manual/identity-dev-admission/run-ci.mjs
```

Both existing CI test jobs run this mandatory gate before their dependent candidate/image publishing jobs. `run-ci.mjs` requires all three test files, at least83 successful cases, and zero skipped, todo, cancelled or failed cases. Missing dependencies, a missing Apache/PHP SAPI, partial test selection or an empty suite cannot pass. The A1 admission feature branch trigger runs tests without automatically publishing an image.

This fixture is separate from `make test`, whose current recipe executes inside the configured business API container. It does not replace that repository check before a PR; this patch produces no PR or cloud deployment.

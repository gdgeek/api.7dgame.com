# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AR 创作平台后端，基于 **Yii2 Advanced** 模板，PHP 8.4+，提供 RESTful API。主要业务逻辑在 `advanced/api/modules/v1/`。

## Common Commands

All commands run from the repository root:

```bash
# Start local environment (first time or after clean)
./scripts/docker/start-docker.sh

# Day-to-day Docker control
make start / make stop / make logs-api / make shell

# Database migrations
make migrate                                          # run all pending
docker-compose exec api php yii migrate/create NAME  # create new migration
docker-compose exec api php yii migrate/down          # rollback one

# RBAC
make rbac   # re-initialize roles/permissions

# Tests
make test                                             # all Codeception tests
docker-compose exec api vendor/bin/codecept run unit  # unit only
cd advanced && php vendor/bin/phpunit -c phpunit.xml --testdox  # PHPUnit (used in CI)

# Cache
docker-compose exec api php yii cache/flush-all

# Email test (inside container)
docker exec -it api7dgamecom-api-1 php yii email-test/all your@email.com
```

## Architecture

### Directory Layout

```
advanced/
  api/
    controllers/         # root-level controllers (SwaggerController, HealthController)
    modules/v1/
      controllers/       # all REST business controllers
      models/            # API-layer models (extend or wrap common models)
      components/        # module-specific components (RefreshToken, etc.)
    web/swagger-ui/      # Swagger UI static assets
    config/              # api app config (main.php, params.php)
  common/
    models/              # shared AR models
    components/          # shared services & utilities
      security/          # RateLimiter, CredentialManager, SafeFileTarget, etc.
  console/
    controllers/         # yii CLI commands (RbacController, EmailTestController, etc.)
    migrations/          # all DB migrations
  tests/
    unit/                # PHPUnit + Codeception unit tests
    integration/         # Codeception integration tests
    manual/              # manual verification scripts (test_*.php)
files/                   # -local.php config snapshots (checked-in templates)
  api/config/            # main.php (routes), params.php (JWT config, etc.)
  common/config/         # main-local.php (DB DSN), params-local.php
docker/                  # Dockerfile, nginx configs, init.sql
```

### Config Loading Order

Yii2 merges configs in this order (later overrides earlier):
```
common/config/main.php
common/config/main-local.php   ← database credentials, environment overrides
api/config/main.php            ← routes, components (CORS, rateLimiter, urlManager)
api/config/main-local.php
```

The `files/` directory contains checked-in templates for the `-local.php` files. In Docker, these are mounted/copied to `advanced/*/config/`.

### Authentication Flow

- JWT access tokens via `bizley/jwt` + `lcobucci/jwt` with EC keys in `jwt_keys/`
- Refresh tokens stored on the `User` model, returned by `AuthController::actionRefresh`
- Every authenticated controller must use `CompositeAuth + JwtHttpBearerAuth` (see AGENTS.md for the required `behaviors()` template)
- `AuthController` and `WechatController` are public (no JWT required)
- Rate limiting on login: 5 attempts / 15 min (configured in `files/api/config/main.php` → `rateLimiter` component)

### Key Custom Components

| Component | Location | Purpose |
|---|---|---|
| `CynosDbConnection` | `common/components/` | Tencent CynosDB-aware DB connection |
| `RateLimiter` / `RateLimitBehavior` | `common/components/security/` | Per-action IP/user rate limiting |
| `CredentialManager` | `common/components/security/` | Centralized credential retrieval |
| `RedisSecurityComponent` | `common/components/security/` | Token revocation & Redis-backed rate limiting |
| `SafeFileTarget` | `common/components/security/` | Log sanitizer (strips credentials from logs) |

### Model Conventions

- `common/models/` holds base AR classes (maps to DB tables)
- `advanced/api/modules/v1/models/` holds API-layer models that either extend common models or add API-specific validation/search logic
- `User` in `api\modules\v1\models\User` implements `IdentityInterface` and owns `token()` / `findByRefreshToken()`
- Search models (e.g. `MetaSearch`, `VerseSearch`) follow the Yii2 `search()` + `ActiveDataProvider` pattern

### Adding a New API Endpoint

1. Create controller in `advanced/api/modules/v1/controllers/`, extending `ActiveController` or `\yii\rest\Controller`
2. Add the standard `behaviors()` block with JWT auth + AccessControl (see AGENTS.md)
3. Register routes in `files/api/config/main.php` under `urlManager.rules` using `yii\rest\UrlRule`
4. Add OpenAPI `@OA\*` annotations on the class and each action
5. If Swagger doesn't auto-scan it, add the file path to `SwaggerController::actionJsonSchema()`

### Swagger / OpenAPI

- Served at `/swagger` (protected by HTTP Basic Auth)
- Generated at request time by `api/controllers/SwaggerController.php` scanning controller files
- Annotations use `OpenApi\Annotations as OA` (doctrine annotation style, not PHP 8 attributes)

### Testing Layout

- Unit tests: `advanced/tests/unit/models/` and `advanced/tests/unit/services/`
- Integration tests: `advanced/tests/integration/` (full flow tests, e.g. `EmailVerificationFlowTest`, `PasswordResetFlowTest`)
- PHPUnit config: `advanced/phpunit.xml`
- Codeception config: `advanced/codeception.yml`

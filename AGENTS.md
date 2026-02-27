# Repository Guidelines

## 项目结构与模块组织
核心业务代码位于 `advanced/`（Yii2 Advanced 模板）：
- `advanced/api`：REST 接口（主要是 `modules/v1` 下的控制器与模型）。
- `advanced/common`：公共组件、模型和配置。
- `advanced/console`：数据库迁移与命令行任务。
- `advanced/tests`：自动化测试与手动测试脚本。

仓库根目录常用支撑目录：
- `docker/`、`docker-compose.yml`：本地与部署容器配置。
- `scripts/`：Docker、CI、邮件等脚本。
- `docs/`：API、Docker、功能说明文档。
- `files/`：环境相关配置快照。

## 构建、测试与开发命令
默认在仓库根目录执行：
- `./scripts/docker/start-docker.sh`：一键初始化并启动本地环境。
- `docker-compose up -d`：启动 API、数据库、Redis 等服务。
- `make logs-api`：查看 API 实时日志。
- `make migrate`：在容器内执行数据库迁移。
- `make rbac`：初始化 RBAC 权限数据。
- `make test`：运行 Codeception 测试集。
- `cd advanced && php vendor/bin/phpunit -c phpunit.xml --testdox`：运行 CI 使用的 PHPUnit 单元测试。

## 代码风格与命名规范
- 沿用现有 Yii2/PHP 风格：4 空格缩进、大括号换行、整体接近 PSR-12。
- 类名使用 `PascalCase`，方法/属性使用 `camelCase`，常量使用 `UPPER_SNAKE_CASE`。
- 控制器统一以 `Controller` 结尾；测试文件以 `Test` 结尾（Codeception 场景测试使用 `*Cest`）。
- 迁移文件使用 Yii 时间戳命名，例如 `m260211_190000_unify_json_columns.php`。

## 控制器权限约定（Yii2-RBAC）
- 所有业务控制器都必须实现统一权限行为，至少包含 `CompositeAuth + JwtHttpBearerAuth + AccessControl`。
- 新增控制器或重构控制器时，`behaviors()` 必须包含以下模板（可按业务补充 `except`）：

```php
$behaviors = parent::behaviors();

// unset($behaviors['authenticator']);
$behaviors['authenticator'] = [
    'class' => CompositeAuth::class,
    'authMethods' => [
        JwtHttpBearerAuth::class,
    ],
    'except' => ['options'],
];

$behaviors['access'] = [
    'class' => AccessControl::class,
];

return $behaviors;
```

## 测试规范
- 当前测试框架为 Codeception（`advanced/codeception.yml`）和 PHPUnit（`advanced/phpunit.xml`）。
- 单元测试放在 `advanced/tests/unit/<domain>/`。
- 手动验证脚本放在 `advanced/tests/manual/`，文件名使用 `test_*.php`。
- 提交 PR 前至少执行 `make test`，并补充相关定向测试（如 `php vendor/bin/codecept run unit`）。

## 提交与 Pull Request 规范
- 提交信息建议沿用历史约定：`fix:`、`feat:`、`refactor:`、`update:`，可加作用域（如 `fix(test): ...`）。
- 标题需明确描述行为变化，中文或英文均可，但避免笼统描述。
- PR 应包含：问题背景、关键改动、是否涉及迁移、测试命令与结果、关联任务/Issue。
- 若变更 API 行为，请同步更新 `docs/` 文档和控制器内 Swagger 注解。

## 安全与配置建议
- 不要提交密钥或凭据；优先使用 `.env.docker`（由 `.env.docker.example` 复制）与本地 `*-local.php` 配置。
- `jwt_keys/` 仅用于本地或受控环境，密钥泄露或共享后应立即轮换。

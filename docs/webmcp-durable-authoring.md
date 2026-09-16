# WebMCP 持久任务与新建幂等

本次新增 `m260917_010000_add_webmcp_authoring_tasks`，依赖已经部署的 P1 `webmcp_operation` 表。只在请求带 UUID `Idempotency-Key` 时启用新的实体／场景创建契约，不带该头的旧客户端保持原接口行为。

## 新建对象

- `POST /v1/metas` 或 `/v1/verses`：请求体带稳定的 `uuid`，请求头带原 `Idempotency-Key`，不带 `If-Match`。
- `GET /v1/metas/create-operations/{operationId}` 或 `/v1/verses/create-operations/{operationId}`：无需预先知道新对象 ID。
- 返回 `id`、`uuid`、`serverRevision`、`writeReceipt`、`replayed`。回执包括原操作 ID、目标类型／ID、`action=create`、`status=completed`、创建时版本和 UUID。

创建、关系更新及回执处于同一事务，唯一键仍是 `(actor_id, operation_id)`。同一键和相同内容返回原结果；同键不同内容／动作返回 409。事务失败一并回滚。不同键属于不同操作，不能用换键代替重试。

查回执和重放都重新检查原调用者及对象当前权限；删除后返回 404，撤权返回 403，不重新创建。404 未观察到回执不能证明原请求未提交。返回的是创建时版本，后续编辑前必须重新读取当前对象版本。

## 持久任务 API

| 请求 | 内容 | 结果 |
| --- | --- | --- |
| `POST /v1/authoring-tasks` | `taskId`、`name`、`steps` | 保存不可变计划和初始进度；同 ID 不同计划 409 |
| `GET /v1/authoring-tasks?offset=0` | 当前账号 | 每页 20 项和 nextOffset |
| `GET /v1/authoring-tasks/{id}` | 当前账号 | 计划、进度、revision、leaseUntil |
| `POST /v1/authoring-tasks/{id}/claim` | `revision`、UUID `claimId` | 取得 120 秒执行租约并增加 revision |
| `PUT /v1/authoring-tasks/{id}/checkpoint` | `revision`、`claimId`、`progress`、可选 `release` | 按原版本／租约保存进度；release 清除租约 |

进度为 `{index,status,states}`；states 保持原计划步骤数量及 key，包含结果、operationId、目标。服务端不执行工具，也不把客户端结果认证为保存成功；写入是否成功必须查询真正的操作回执。原始 JSON 中的空对象与空数组在工具输入及结果中保持区别。

JWT、路由 RBAC、任务所有者校验同时生效。任务路由继承现有实体／场景 create 或 update 路由权限，新建回执路由继承对应 create 权限。只有任务原账号可读取和推进；记录是该账号先前取得的证据，读取历史记录不等于仍能访问或编辑原对象。

最大 30 步、256 KB 计划、1 MB 进度、每账号 200 条任务。当前没有自动清理和后台执行器；达到配额后拒绝创建，需要制定保留／清理策略。任务租约仅保护协作进度；写入事务仍由对象自己的幂等键及内容版本保护。

## 精确迁移与发布顺序

```sh
php yii web-mcp-authoring-migrate/plan
php yii web-mcp-authoring-migrate/up 1 --interactive=0
php yii web-mcp-authoring-migrate/plan
```

命令只选择本次迁移，不运行其他待执行迁移。仅在部署时的受控容器内运行；此次开发没有执行线上迁移。迁移校验表结构、主键和 MySQL InnoDB；保留数据，safeDown 不删除任务／回执。

先备份涉及表结构及 RBAC 项，在开发后端精确迁移并部署，检查双入口都能读回同一个任务与创建回执，再部署前端。新前端要求服务端任务／创建回执接口，接口缺失时明确失败，不降级为内存成功。开发环境完整业务验收通过后才能推进 main、publish。回退前端／应用代码时保留新增表与证据。

## 验证

- `php vendor/bin/phpunit -c phpunit.xml tests/unit/webmcp`：54 项通过，覆盖严格路由、预检路由、JWT/RBAC 配置、精确迁移、归属、租约、冲突、回滚、删对象及撤权。
- 已在本机临时 MySQL 8.4 空库执行下面两份集成测试，10 项通过：独立 PDO 并发创建仅一份对象／回执；并发任务抢占；提交成功后代理丢弃全部 HTTP 响应；按键恢复；JSON 类型；账号隔离和撤权。HTTP 夹具使用测试身份，认证行为另由控制器配置／路由测试覆盖，不代替线上 JWT 验收。

```sh
WEBMCP_MYSQL_TEST_DSN='mysql:host=127.0.0.1;port=33079;dbname=webmcp_test_example' \
php vendor/bin/phpunit -c phpunit.xml \
  tests/integration/WebMcpMySqlConcurrencyTest.php \
  tests/integration/WebMcpHttpRecoveryTest.php --fail-on-skipped
```

集成测试只允许明确的 `webmcp_test_*` 库，会清空其中夹具表；禁止指定业务库。CI 已运行这两份测试，新用例自动进入既有 CI。

本机全量 PHPUnit 在修改前后均有同一组 172 个环境错误（MySQL／Redis 等依赖缺失），没有新增错误；不能据此宣称后端全量通过。`make test` 依赖项目 Docker api 容器，本次未在业务容器运行。正式发布仍要通过配置齐全的 CI 和开发环境功能验收。

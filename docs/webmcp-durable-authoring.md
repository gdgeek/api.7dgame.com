# WebMCP 持久任务与新建幂等

本次新增 `m260917_010000_add_webmcp_authoring_tasks`，依赖已经部署的 P1 `webmcp_operation` 表。只在请求带 UUID `Idempotency-Key` 时启用新的实体／场景创建契约，不带该头的旧客户端保持原接口行为。

## 新建对象

- `POST /v1/metas` 或 `/v1/verses`：请求体带稳定的 `uuid`，请求头带原 `Idempotency-Key`，不带 `If-Match`。
- `GET /v1/metas/create-operations/{operationId}` 或 `/v1/verses/create-operations/{operationId}`：无需预先知道新对象 ID。
- 返回 `id`、`uuid`、`serverRevision`、`writeReceipt`、`replayed`。回执包括原操作 ID、目标类型／ID、`action=create`、`status=completed`、创建时版本和 UUID。

创建、关系更新及回执处于同一事务，唯一键仍是 `(actor_id, operation_id)`。同一键和相同内容返回原结果；同键不同内容／动作返回 409。事务失败一并回滚。不同键属于不同操作，不能用换键代替重试。

查回执和重放都重新检查原调用者及对象当前权限；删除后返回 404，撤权返回 403，不重新创建。404 未观察到回执不能证明原请求未提交。返回的是创建时版本，后续编辑前必须重新读取当前对象版本。

### U01：只读创建恢复查询

`GET /v1/metas/create-operations?operationId={UUID}&creationUuid={UUID}`（场景替换为 `verses`）至少提供一个标识，无需对象 ID。集合路由复用已有 `create-operation` action 与 RBAC，不需要新增迁移。原 `/{operationId}` 回执接口保持兼容。JWT、原账号回执归属与当前对象编辑权限仍必须通过；UUID 查找额外限定 `author_id=当前账号`（实体排除 prefab）。其他账号的 UUID 不暴露对象身份，同一账号多条 UUID 命中返回冲突而不选择第一条。

响应为 `contractVersion=creation-recovery-v1`，回显 `targetType`、`operationId`、`creationUuid`，并始终返回 `retrySafe=false`：

| status / reason | 证据及语义 |
| --- | --- |
| `completed / creation_receipt` | 原回执已找到且权限通过；`verification=server_acknowledged`、`operationVerified=true`；返回原 `writeReceipt`、真实 `id/uuid`、创建时 `serverRevision`、`requestHash` 与 `recordedAt` |
| `observed / uuid_match_without_receipt` | 仅找到当前账号可编辑的唯一 UUID 对象；`verification=uuid_readback`、`operationVerified=false`；返回 `id/uuid/currentRevision`，**不含** `writeReceipt` 或创建时 `serverRevision` |
| `not_observed / no_accessible_creation_evidence` | 没有本账号可读的证据；不代表原请求未执行，也不允许重放创建 |
| `indeterminate / permission_denied`、`created_object_unavailable` | 当前权限不足，或原回执指向的对象不可用；不暴露对象 ID，不退回 UUID 候选代替原结果 |
| `conflict / operation_key_conflict`、`creation_uuid_mismatch`、`ambiguous_uuid` | 原键属于其他动作/类型、UUID 与原回执不符、或 UUID 非唯一；不返回候选对象身份 |

未认证、路由无权限、无此端点或服务故障仍可返回 HTTP 错误。客户端分别公开 `authentication_required`、`permission_denied`、`receipt_or_endpoint_not_observed`、`query_unavailable` 等原因，并保留 `operationStatus=indeterminate`。HTTP 404 也可能表示旧部署缺少端点，不能当作“确定未创建”。

WebMCP `xrugc_get_authoring_operation` 支持 `kind + operationId/creationUuid` 跨会话只读查询；UUID 命中先返回 candidate，原操作仍为 unknown。显式 `xrugc_reconcile_authoring_creation` 可用原 `kind + creationUuid`（可附 operationId）恢复对象引用，不需要对象 ID；其 `status=completed` 只代表核对完成，`verification=uuid_readback`、`operationVerified=false`、`operationStatus=indeterminate` 保留原写入未确认的事实，不生成原回执。

旧客户端未发送幂等头的创建不会被追溯补成持久回执；找不到旧 UUID 也可能是对象删除、归属变化或原请求尚在事务中。当前实现不提供持久“处理中”或“确定未执行”保证。前端能力声明将部署端幂等可用性标为 `null / unverified`，客户端支持、历史 UUID 恢复范围另列，不能仅凭函数已注册宣称后端已部署。

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

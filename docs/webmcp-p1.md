# WebMCP P1：操作回执、重复请求去重与版本冲突保护

## 当前范围

P1 保留三项保证：超时后查询已提交结果、重复请求去重、阻止携带旧版本的写入覆盖新内容。新增且仅新增 `webmcp_operation` 一张业务表。主站 Unity 运行器整合与本可靠性增强分别交付，原 WebGL 插件、Blockly/Three.js 外部库保持不变。

固定历史发布快照、内容归档哈希、历史版本读取与回滚移到 P2，不在本 PR 的迁移、路由和发布路径中。

## 写入合同

实体/场景保存、实体/场景脚本保存、场景发布共五个已有写入入口接入 `ReliableWrite`。目标主记录在事务中加行锁，校验权限和版本，再将业务写入与回执一起提交。异常回滚二者。旧调用仍可不带两个请求头，但也参与同一主记录行锁。

GET 实体/场景返回 `serverRevision`，它是该主记录及其脚本的内容摘要。前端必须使用读取时的版本，不能提交前重新读取版本来掩盖冲突。

| 请求头 | 内容 |
| --- | --- |
| `Idempotency-Key` | 一次意图的 UUID，同一意图重试保持不变 |
| `If-Match` | 带双引号的 `serverRevision`，如 `"sha256:…"` |

请求头必须成对出现。无效参数返回 400；旧版本、同一操作 ID 对应不同请求返回 409。相同账号、操作 ID、目标、动作、版本、请求体重试返回原回执，不再次写入。重复响应只保证回执字段，不重新序列化现在的可变业务对象。客户端不得自动重试、覆盖 409 或把网络超时解释为失败。

回执字段：`operationId`、`status: completed`、`targetType`、`targetId`、`action`、`serverRevision`。发布还包含 `snapshotId`。回执不保存 token、签名 URL、请求体、错误正文或快照正文。

## 独立查询与发布语义

- `GET /v1/metas/{id}/operations/{operationId}`
- `GET /v1/verses/{id}/operations/{operationId}`
- `GET /v1/verses/{id}/publication`

均保留 JWT、RBAC 和当前目标可编辑检查。操作回执还要求账号与原提交人相同。404 只表示当前未观察到已提交回执，也可能是事务仍在执行；它不是失败证明。查询不重放请求。

发布继续使用现有可变 Snapshot，与操作回执在同一事务提交。`publication` 根据当前 Snapshot 返回发布状态、snapshotId 和 snapshotUuid，标为 `verification: current_snapshot`，旧 `verseRelease` 字段不作为依据。未发布返回 published=false。查询不需要归档表。

旧操作回执证明当次发布曾提交成功；后续发布即使复用 Snapshot ID，也不改变旧回执或让相同操作重试重新发布。但旧回执不保存当时的快照内容，当前 Snapshot 查询不证明历史内容没有变化。前端保留 `readBackVerified: false`，不宣称已读回固定历史版本。

## 保证的边界

- 幂等与乐观锁覆盖上述五个 API 中带两个请求头的调用。资源重命名、上传、其他管理接口及旧客户端不带请求头的请求没有乐观锁承诺。
- 版本保护的是实体/场景主记录及其脚本；场景引用的其他实体/资源没有跨对象统一锁定。当前不是事务性的创作版本库。
- 页内确认、取消和未提交操作没有持久化后端回执。提交后重载可查询服务器结果，不能恢复编辑器的全部内存状态。
- 当前不自动清理操作回执。去重与重试有效期、压缩/清理策略需另行制定，不能直接删除回执导致旧请求重复执行。

## 上线顺序与精确迁移

先准备数据库和后端，再切换 P1 web。新 web 缺少版本或匹配回执时拒绝把保存记为成功。

1. 备份数据库，用包含本变更的后端代码/镜像在连接目标应用数据库的维护容器或服务器中运行下方命令。先运行维护进程，不把未迁移的新后端先切到流量入口。
2. 确认 `WEBMCP_P1_SCHEMA=READY` 和再次 plan 的 `ALREADY_APPLIED`。此入口只执行 P1 一个迁移，不执行 Task 5.1 等其他待迁移内容。
3. 切换后端，验证带权限的 GET 对象返回 serverRevision、新只读路由可用，再发布 web。
4. 使用独立测试对象验收保存、重复请求、旧标签页冲突、长确认等待、重载查回执以及发布回执与当前快照的区别。

在后端 `advanced/` 目录执行：

```bash
php yii web-mcp-p1-migrate/plan
php yii web-mcp-p1-migrate/up 1 --interactive=0
php yii web-mcp-p1-migrate/plan
```

不要用不加范围的 migrate/up 代替此入口。迁移只新增 webmcp_operation 及只读路由 RBAC。MySQL DDL 部分完成时可重跑补缺失索引；同名但非唯一/列不匹配的索引会失败。已记录迁移但缺少必要表/列/唯一约束时，plan/up 不能声称 schema 就绪。

回滚先退回旧 web，再退后端应用，保留操作回执表。本迁移不提供删除历史的 down。P2 必须使用独立的新迁移，不回改已经上线的 P1 迁移。

## 验证

```bash
php advanced/vendor/bin/phpunit --do-not-cache-result -c advanced/phpunit.xml \
  advanced/tests/unit/webmcp \
  advanced/tests/unit/controllers/VersePreviewContractTest.php \
  advanced/tests/unit/models/SnapshotTest.php
```

MySQL 并发测试必须显式连接名为 webmcp_test_* 的专用可清空数据库，不得指向开发/生产库。CI 自动创建独立测试库，并要求两个测试不能跳过：同键并发只提交一次；不同键旧版本竞争只有一个成功。

```bash
WEBMCP_MYSQL_TEST_DSN='mysql:host=127.0.0.1;port=3306;dbname=webmcp_test_ci' \
WEBMCP_MYSQL_TEST_USER=root WEBMCP_MYSQL_TEST_PASSWORD='<test-only-password>' \
php advanced/vendor/bin/phpunit --do-not-cache-result -c advanced/phpunit.xml \
  advanced/tests/integration/WebMcpMySqlConcurrencyTest.php --fail-on-skipped
```

### 真实 HTTP 丢响应恢复测试

`advanced/tests/integration/WebMcpHttpRecoveryTest.php` 使用 loopback HTTP 服务和实际 MySQL 事务。故障代理先完整接收上游成功回执，再断开客户端连接，不向客户端转发响应字节；随后通过生产 `VerseController::actionOperation` 查询原 operationId，核对内容、revision 和回执，并重复原请求，验证只有一条回执且业务计数只递增一次。另覆盖 HTTP 409 拒绝旧版本、其他可编辑账号不能读原账号回执，以及撤销当前编辑权限后的 403。

测试仅接受 loopback、`webmcp_test_*` 专用数据库，并在清理测试表前核对实际连接库名。它只使用测试身份，不加载应用配置、真实 JWT 或签名密钥；HTTP 写入经过生产 `ReliableWrite`，业务变化使用最小测试 mutation。此证据不代替真实浏览器四页序列化、JWT/RBAC 中间件或线上双后端数据库一致性验收。

沿用上述三个 `WEBMCP_MYSQL_TEST_*` 测试环境变量执行，CI 将两种集成测试都设为不能跳过：

```bash
php advanced/vendor/bin/phpunit --do-not-cache-result -c advanced/phpunit.xml \
  advanced/tests/integration/WebMcpHttpRecoveryTest.php --fail-on-skipped
```

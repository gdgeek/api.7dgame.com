# WebMCP P1：写入回执、并发保护与发布证据

## 行为

实体/场景保存、实体/场景脚本保存、场景发布共五个已有写入入口接入 `ReliableWrite`。目标主记录在事务中加行锁，校验权限和版本，再将业务写入与回执一起提交。异常回滚二者。旧调用仍可不带两个请求头，但也参与同一主记录行锁。

GET 实体/场景返回 `serverRevision`。它是该主记录及其脚本的内容摘要，前端必须保存读取时的版本，不能提交前重新读取版本来掩盖冲突。

| 请求头 | 内容 |
| --- | --- |
| `Idempotency-Key` | 一次意图的 UUID；同一意图重试保持不变 |
| `If-Match` | 带双引号的 `serverRevision`，如 `"sha256:…"` |

请求头必须成对出现。无效参数返回 400；旧版本、同一操作 ID 对应不同请求返回 409。相同账号、操作 ID、目标、动作、版本、请求体重试返回原回执，且不再次写入。重复响应只保证回执字段，不重新序列化现在的可变业务对象。客户端不得自动重试、覆盖 409 或把网络超时解释为失败。

回执字段：`operationId`、`status: completed`、`targetType`、`targetId`、`action`、`serverRevision`。发布还包含 `snapshotId`、`publicationRevision`、`contentHash`。不保存 token、签名 URL、请求体或错误正文。

## 独立查询

- `GET /v1/metas/{id}/operations/{operationId}`
- `GET /v1/verses/{id}/operations/{operationId}`
- `GET /v1/verses/{id}/publication`
- `GET /v1/verses/{id}/publication/{revision}`

均保留 JWT、RBAC 和当前目标可编辑检查。操作回执还要求账号与原提交人相同。404 只表示当前未观察到已提交回执，也可能是事务仍在执行；它不是失败证明。查询不重放请求。

`publication` 根据实际 Snapshot 返回发布状态，旧 `verseRelease` 字段不作为依据。每次新发布同时归档不可变 JSON、独立 UUID 版本和 SHA-256。按版本读取时先重新校验归档正文哈希，再返回完整快照。后续发布即使复用原 Snapshot ID，也不会覆盖旧版本归档。

迁移以前的快照仍返回 `published: true`，但没有归档版本/内容哈希，标为 `legacy_snapshot`。不能回填一个现在的快照然后声称验证了历史发布。若外部程序改写可变 Snapshot，最新状态只有在存储内容哈希匹配归档时才返回该归档版本。

## 范围

- 幂等与乐观锁覆盖上述五个 API，保护实体/场景自身记录和脚本。资源重命名、上传、其他管理接口及旧客户端不带请求头的请求没有乐观锁承诺。
- 场景引用的其他实体/资源没有被一起锁定；本功能不是跨资源的事务性创作版本库。归档记录本次实际生成的完整快照。
- 归档内容可能含发布资源地址，按现有编辑权限保护；不会将快照正文放进操作回执或浏览器操作历史。旧资源链接过期不影响归档内容哈希，但实际资源仍需运行验收。
- 保留原 WebGL 插件。没有修改 Blockly、Three.js 外部库。

## 上线顺序与精确迁移

必须先准备数据库和后端，再切换 P1 web。新 web 缺少版本或匹配回执会拒绝把保存记为成功。

1. 备份数据库；使用包含本变更的后端代码/镜像，在连接目标应用数据库的维护容器或服务器中运行下方命令。只启动维护进程，不先把未迁移的后端切到流量入口。
2. 确认 `WEBMCP_P1_SCHEMA=READY` 和再次 plan 的 `ALREADY_APPLIED`。该入口只执行 P1 一个迁移，不执行 Task 5.1 等其他待迁移内容。
3. 切换后端，验证带权限的 GET 对象返回 `serverRevision`，新只读路由可用，再发布 web。
4. 使用独立验收对象执行保存、重复请求、旧标签页冲突、确认等待与发布版本读回。记录实际镜像、前端构建、账号角色、对象及操作 ID。

在后端 `advanced/` 目录执行：

```bash
php yii web-mcp-p1-migrate/plan
php yii web-mcp-p1-migrate/up 1 --interactive=0
php yii web-mcp-p1-migrate/plan
```

不要用不加范围的 `migrate/up` 代替此入口。迁移只新增 `webmcp_operation`、`scene_publication_revision` 及只读路由 RBAC。MySQL DDL 部分完成时可重跑来补缺失索引；同名但非唯一/列不匹配的索引会明确失败。已记录迁移但缺少必要表/列/唯一约束时，plan/up 都不能声称 schema 就绪。

回滚先退回旧 web，再退后端应用。保留回执和归档表；本迁移不提供破坏历史的 down。当前没有自动清理策略，容量管理需保留幂等去重记录和发布审计需求后另行制定。

## 验证命令

```bash
php advanced/vendor/bin/phpunit --do-not-cache-result -c advanced/phpunit.xml \
  advanced/tests/unit/webmcp \
  advanced/tests/unit/controllers/VersePreviewContractTest.php \
  advanced/tests/unit/models/SnapshotTest.php
```

MySQL 并发测试必须显式连接名为 `webmcp_test_*` 的专用可清空数据库，不得指向开发/生产库。CI 自动创建独立测试库，并要求两个测试不能跳过：同键并发只提交一次；不同键旧版本竞争只有一个成功。

```bash
WEBMCP_MYSQL_TEST_DSN='mysql:host=127.0.0.1;port=3306;dbname=webmcp_test_ci' \
WEBMCP_MYSQL_TEST_USER=root WEBMCP_MYSQL_TEST_PASSWORD='<test-only-password>' \
php advanced/vendor/bin/phpunit --do-not-cache-result -c advanced/phpunit.xml \
  advanced/tests/integration/WebMcpMySqlConcurrencyTest.php --fail-on-skipped
```

# WebMCP P2 发布历史合同与部署

2026-09-13：P2 开发完成，尚未执行业务数据库迁移或线上发布。独立迁移为 `m260913_120000_add_scene_publication_history`；P1 迁移保持不变。

## 发布和一致性

`VerseController::actionTakePhoto` 通过 ReliableWrite 持有场景行锁，在 MySQL REPEATABLE READ 主库事务的一致性读视图内捕获场景、选定语言运行代码、共享实体、资源引用和空间。所有参与表必须为 InnoDB。原始 Query 避免 ActiveRecord afterFind 写入及查询缓存；新 Snapshot 和归档来自同一份捕获内容。

Snapshot、保留期内正文不可变的 `scene_publication_revision` 和 P1 成功回执一起提交，任一写入失败全部回滚。相同操作者和 Idempotency-Key 的重试回放原 publicationVersionId/contentHash；换语言属于不同意图，冲突拒绝。无操作键旧客户端仍生成归档，但不承诺网络重试去重。P1 serverRevision 继续约束场景自身编辑，不等于全部共享依赖版本；共享依赖按本次事务一致性读视图捕获。

原 `/publication` 继续指向当前可变 Snapshot；每次发布新增的 UUID 才是历史版本。旧发布不事后冒充原件补录。

## 合同

- `GET /v1/verses/{id}/publications?limit=20&before=0`：limit 1–50，before 为返回的 nextBefore；列表仅元数据，包含数量、正文总字节、容量警告。首次无记录为 history_unavailable。
- `GET /v1/verses/{id}/publications/{publicationVersionId}`：元数据及 canonicalBody，服务端核验字节长度、SHA256、场景/语言/schema 和必要正文结构。不存在为 404，损坏为 publication_corrupt，禁止回退当前 Snapshot。
- 发布响应及 P1 回执新增四个可选字段：publicationVersionId、contentHash、schemaVersion、language。成功提交与随后读回核验分开；前端读回失败仅可重试 GET；若返回 410 则该正文已过期，应重新列出保留版本，不再重试该版本。
- 新路由沿用 JWT、RBAC 和当前场景 editable 对象权限。授权撤销后不可读取；归档不像操作回执那样仅限原发布人。路由源在 `files/api/config/main.php`，部署必须核对实际挂载后的路由。

canonicalBody 为 schemaVersion 1 的 UTF-8 JSON 原文，哈希为 `sha256:` 加原文字节摘要。对象键排序，数组保持顺序，Unicode/斜杠不转义，保留小数零。客户端直接对收到的原文计算哈希，不重新 JSON 序列化。PHP 与 JS 共用 `publication-v1.json` 字节样例。

正文固定名称、描述、封面文件引用、场景及实体运行数据/选定语言代码、managers、资源和空间引用。平台文件字段去掉访问 url，保留 id/key/md5 等引用；用户自行写入 data/code/info 的字符串仍按原内容归档，可能包含 URL。没有复制资源二进制、全部语言或 Blockly 编辑源；`resourceBytesArchived=false`，资源是否仍可获取须另行核验。哈希证明正文匹配，不提供防管理员改库的签名证明。

## 容量与保留

单条正文 8 MiB（超限 413），保留正文每场景合计 512 MiB（超限 507），400 MiB 预警。超过限制时拒绝整个发布，不留下部分成功。2026-09-19 增加数量保留策略，详见下节。生产启用前测量代表性场景正文和日发布量，确认此初始政策适用、MySQL max_allowed_packet 足够容纳正文加协议开销。

2026-09-19 起采用下述最近 20 份正文策略，替代首期不自动清理的约定。保留期内正文仍不可变；过期正文清空，只保留小型版本标识和校验信息。场景删除不级联清理历史，场景不存在后查询返回 404；管理员仍需监控少量元数据和操作回执的增长。

## 精确上线步骤

1. 核对 xrteeth / tmrpp 两后端生产与开发各自数据库落点、旧版本、15 张参与表 InnoDB、备份及恢复依据。不要用部署管理域名代替业务 API。
2. 在目标容器中准备新代码但先不启用流量，执行 `php yii web-mcp-p2-migrate/plan`。仅允许返回本次精确迁移或 ALREADY_APPLIED；不运行批量 migrate。
3. 在确认过的目标数据库执行 `php yii web-mcp-p2-migrate/up 1 --interactive=0`；再执行 `php yii web-mcp-p2-migrate/verify`。verify 核对结构、正文类型/校对规则、索引、引擎、迁移和 RBAC。相同物理库只需迁移一次，两入口分别 verify。
4. 后端上线后，以独立验收场景验证旧客户端发布、A/B 固定历史、同键去重、丢响应查回执、撤权拒绝及双入口同内容。新后端在缺表/非事务存储时会拒绝发布，因此必须先迁移再接流量。
5. 再推进前端 develop CI、main/publish 镜像和容器；核对两个主站入口、网站指南 1.1.0 及归档只读工具。不得把本地合成 UI 测试算作线上验收。

回退先前端、再后端代码，保留新表、迁移记录和归档。safeDown 明确不删除证据。旧代码回退期间的新发布不具备 P2 历史保证；恢复新代码前需记录这段缺口。

## 自动化验证

新增 PublicationArchiveTest、PublicationMySqlTest，扩展 ReliableWriteTest 和真实 HTTP 丢响应测试。后端 CI 加入 PublicationMySqlTest 且 --fail-on-skipped。集成测试只接受专用 webmcp_test_* 临时数据库，并校验实际数据库名称。

已覆盖事务失败回滚、A/B 历史、旧客户端、同键/异键并发、保存/发布并发、共享依赖一致读、非 InnoDB 拒绝、丢成功响应后读回与重试、容量/分页、权限/损坏和严格路由。实际生产双后端 HTTP/JWT 与迁移验收尚待上线批次执行。


## 2026-09-19：默认保留 20 份正文

- `WEBMCP_PUBLICATION_RETAINED_VERSIONS`：默认 `20`，仅接受整数 `1..64`。双后端必须配置一致。无须数据库迁移；配置非法时返回 503 `publication_retention_configuration_invalid`，不静默取消限额。
- 历史列表只列仍保留正文，`total/totalBytes/nextBefore` 都按保留正文计算，新增 `retention: {maxVersions, expiredVersions, policy: "latest_versions", expiredVersionHttpStatus: 410}`。
- 清理按数据库递增 ID 排序，不用可能相同的秒级时间；包括没有幂等请求头的旧发布入口。相同操作重试仍回放原成功回执，不额外创建、清理或重新发布。
- 410 表示正文已删除，并不推翻原操作的成功事实。AI 应重新列出保留版本，禁止重试该过期正文或自动重新发布；比较、导出也遵循此规则。
- 仅小型元数据行和原有操作回执持续保留，用于明确区分过期/不存在和保持幂等。它们仍有少量增长；20 的上限限制完整正文份数，不是全表行数。此策略不压缩 MySQL 文件，也不保证账单立即下降。

### 首次启用与回退

先在不接收业务发布流量的新代码/一次性容器上，连接目标库运行只读命令：

```sh
php yii web-mcp-publication-retention/plan 0 100
```

输出每场景**下一次成功发布时**会清理的版本 ID、字节数和总可回收正文量（预留一个新版本名额）。如果 `nextAfter` 非空，将其作为第一个参数继续分页。命令不输出正文、凭据或资源 URL，不写数据库；计划不是批准令牌，后续发布可能改变候选名单。

先核对完整计划，再部署到接收流量的双后端。不要先切流量再补预览，因为新代码在下一次发布时就会自动执行清理。已有超额场景只在下次成功发布时收敛；本次没有后台批量清理或场景删除级联逻辑。

发布代码回退不能恢复已经清空的正文；如需长期留存某版本，必须提前导出。旧后端不认识过期标记，会把它报告成正文损坏，所以回退应使用仍识别 410 的兼容代码。已删除正文若无独立备份不可找回。

# WebMCP P2 发布历史合同与部署

2026-09-13：P2 开发完成，尚未执行业务数据库迁移或线上发布。独立迁移为 `m260913_120000_add_scene_publication_history`；P1 迁移保持不变。

## 发布和一致性

`VerseController::actionTakePhoto` 通过 ReliableWrite 持有场景行锁，在 MySQL REPEATABLE READ 主库事务的一致性读视图内捕获场景、选定语言运行代码、共享实体、资源引用和空间。所有参与表必须为 InnoDB。原始 Query 避免 ActiveRecord afterFind 写入及查询缓存；新 Snapshot 和归档来自同一份捕获内容。

Snapshot、insert-only `scene_publication_revision` 和 P1 成功回执一起提交，任一写入失败全部回滚。相同操作者和 Idempotency-Key 的重试回放原 publicationVersionId/contentHash；换语言属于不同意图，冲突拒绝。无操作键旧客户端仍生成归档，但不承诺网络重试去重。P1 serverRevision 继续约束场景自身编辑，不等于全部共享依赖版本；共享依赖按本次事务一致性读视图捕获。

原 `/publication` 继续指向当前可变 Snapshot；每次发布新增的 UUID 才是历史版本。旧发布不事后冒充原件补录。

## 合同

- `GET /v1/verses/{id}/publications?limit=20&before=0`：limit 1–50，before 为返回的 nextBefore；列表仅元数据，包含数量、正文总字节、容量警告。首次无记录为 history_unavailable。
- `GET /v1/verses/{id}/publications/{publicationVersionId}`：元数据及 canonicalBody，服务端核验字节长度、SHA256、场景/语言/schema 和必要正文结构。不存在为 404，损坏为 publication_corrupt，禁止回退当前 Snapshot。
- 发布响应及 P1 回执新增四个可选字段：publicationVersionId、contentHash、schemaVersion、language。成功提交与随后读回核验分开；前端读回失败只能重试 GET。
- 新路由沿用 JWT、RBAC 和当前场景 editable 对象权限。授权撤销后不可读取；归档不像操作回执那样仅限原发布人。路由源在 `files/api/config/main.php`，部署必须核对实际挂载后的路由。

canonicalBody 为 schemaVersion 1 的 UTF-8 JSON 原文，哈希为 `sha256:` 加原文字节摘要。对象键排序，数组保持顺序，Unicode/斜杠不转义，保留小数零。客户端直接对收到的原文计算哈希，不重新 JSON 序列化。PHP 与 JS 共用 `publication-v1.json` 字节样例。

正文固定名称、描述、封面文件引用、场景及实体运行数据/选定语言代码、managers、资源和空间引用。平台文件字段去掉访问 url，保留 id/key/md5 等引用；用户自行写入 data/code/info 的字符串仍按原内容归档，可能包含 URL。没有复制资源二进制、全部语言或 Blockly 编辑源；`resourceBytesArchived=false`，资源是否仍可获取须另行核验。哈希证明正文匹配，不提供防管理员改库的签名证明。

## 容量与保留

首期单条正文 8 MiB（超限 413），每场景合计 512 MiB（超限 507），400 MiB 预警。超过限制时拒绝整个发布，不留下部分成功。生产启用前测量代表性场景正文和日发布量，确认此初始政策适用、MySQL max_allowed_packet 足够容纳正文加协议开销。

默认不自动删除；场景删除不级联删除归档，场景不存在后查询返回 404。恢复、导出、清理及资源字节保留属于后续范围，数据库管理员应纳入容量监控和备份。

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

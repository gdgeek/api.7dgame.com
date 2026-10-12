# 独立 Identity 的 PHP 兼容边界

该实现复用现有 Identity 和 Yii 工程，默认仍为旧模式。它是供隔离验证的增量实现，当前不能作为已完成上线的切换方案。

## 所有权和配置

`IDENTITY_DATA_SOURCE=legacy` 为默认值。显式设置 `identity` 后，身份验证、当前角色/组织、全局权限、账号写入和会话签发只使用 Identity。原有 `AUTH_PROVIDER`、生命周期迁移标记和 fallback 配置不能恢复本地认证或本地写入。Identity 不可用时请求失败，不自动恢复旧库权威。

HTTP 启动还要求 `IDENTITY_INDEPENDENT_READY=true`；默认 `false`，用于阻止尚未通过兼容检查的切换。该值不是测试通过证明，只有完成下面列出的导入、路由、登录态和回滚演练后才可设置。Console 投影命令仅要求数据源为 `identity`，方便维护窗口内先导入再开放 HTTP。

必需配置为 `IDENTITY_AUTH_BASE_URL`、`IDENTITY_IAM_INTERNAL_API_TOKEN`（可继承 `IDENTITY_INTERNAL_API_TOKEN`）和 Auth scope 的 `IDENTITY_TOKEN_ISSUANCE_INTERNAL_API_TOKEN`（可继承 `IDENTITY_ACCOUNT_INTERNAL_TOKEN`，最后继承通用内部 key）。不同内部调用必须与 Identity 对应服务的凭据一致；driver 的 app/api 均转发独立 IAM/Auth key、三个 Apple allowlist 和旧二维码捕获开关。配置源在 `files/api/config`、`files/backend/config` 和 `files/common/config`；修改过的运行时挂载目标已同步。

## 请求和权限

所有 `JwtHttpBearerAuth` 控制器统一由 DI 绑定到 Identity 校验器。新旧 access token 都交给 Identity 验签并读取当前主体状态、会话撤销状态和角色；旧 JWT 内嵌角色不作为授权依据。

`IdentityDbManager` 的全局权限决定、当前 policy graph、直接授权和 rule 元数据来自 Identity。PHP 执行现有 Yii business rule 类，保留场景归属、成员关系等对象 ACL；Identity allow 与对象 rule 必须同时通过。继承权限不会被展平成直接授权，以免跳过中间规则。MDM 对 policy 的修改通过私库事务 RPC，业务库不再保存授权写入；旧批量清空方法在独立模式直接拒绝。

用户管理、组织、密码、邮箱、微信、Person 和旧 REST 账号入口由请求网关转发。`/v1/user/info` 留在 PHP 聚合 Identity 资料与业务余额、积分、头像文件；`/v1/user/update` 只向 Identity 写一次，成功后重新聚合相同完整返回格式。`/server/token` 也由 Identity 签发。旧 `/site/login`、`/v1/site/login` 保留字符串 `auth`，旧未版本微信二维码、轮询、注册和绑定保留各自原响应形状。

Person 保留原 REST 方法、查询参数和分页头，人员头像按 Identity 提供的文件 ID 由 PHP 批量解析业务 File，辅助 ID 不暴露给客户端。

内部协议：

| 接口 | 用途 |
| --- | --- |
| `POST /internal/auth/verify-access-token` | 验签并返回当前 subject |
| `GET /internal/auth/device-sn/accounts?q=&page=&pageSize=` | Auth scope 内部 key 读取私库当前可绑定 SN 账号，排除禁用、删除及继承 root/admin/manager |
| `GET /internal/identity/subjects/:id` | 当前身份、角色、直接/有效权限和组织 |
| `POST /internal/identity/authorize` | Identity 原生全局判权 |
| `GET /internal/identity/events?afterId=&limit=` | 版本化业务显示投影事件 |
| `GET /internal/identity/organizations/:id/members` | 校园业务操作使用的当前组织成员和角色摘要 |
| `POST /internal/apple/{resolve,create,link}` | 已验证 Apple provider 断言及私库挑战/账号/绑定 |
| `POST /internal/legacy-email-link/{validate-reset,reset,verify,request-reset,resend-verification,signup}` | 原邮件链接、邀请码和账号写入 |
| `POST /internal/legacy-php-session/exchange` | 限期转换可信 Yii 会话/签名旧 cookie，随后使用 Identity 会话 |
| `POST /internal/legacy-admin` | 原后台邀请码、用户管理和 MDM policy 读写，内部 Auth key 加当前 actor access token |
| `POST /internal/identity/policy-graph` | 内部 IAM key 读取当前规则和继承图，供 PHP 业务 ACL 执行 |

校园清理、内容导入等业务操作仍由 PHP 执行；其管理者组织归属和目标成员来自 Identity 实时读取，不依赖主库滞后的 `user_organization`。成员摘要只供角色和组织检查，不能替代完整的直接权限读取。

设备 SN 仍是业务数据。PHP 创建、分发和解绑继续操作业务 `device_sn`，账号选择和绑定资格改读 Identity 当前用户及完整继承角色；不再用业务投影或旧 RBAC 决定资格。Identity 使用单独的 `DEVICE_SN_DB_HOST/PORT/NAME/USER/PASSWORD` 只读连接校验设备，部署账号只授予 `SELECT device_sn`；独立模式缺配置或连接失败明确拒绝 SN 认证，普通账号登录不依赖该连接。SN access 的来源只取 Identity 验签返回的 `authContext`，PHP 保留该来源并继续禁止凭据派生及账号管理。启停用设备后的已有 access 保留原最长三小时兼容期，解绑、用户删除/禁用和提升到继承管理角色立即拒绝；refresh 必须再次确认设备启用。

## 主库业务投影

保留原整型 `user.id`，所有业务 FK 继续引用它。主库账号模型禁止保存、删除、改密、验证密码和本地签发。认证主体及业务目标的纯 ID 查找遇到缺失用户时，从 Identity 当前 subject 补建最小用户和空业务资料；不复制密码、认证 key 或 refresh token。应用可配置独立 `identityProjectionDb` 组件供投影写入，常规业务数据库凭据应撤销用户/授权表写权限。

迁移 `m261012_010000_create_identity_business_projection_state` 只增加版本/游标表，默认模式下不修改旧账号凭据。投影执行方式：

```sh
IDENTITY_DATA_SOURCE=identity php yii identity-projection/sync --limit=100
IDENTITY_DATA_SOURCE=identity php yii identity-projection/watch --limit=100 --pollInterval=2 --maxBackoff=30
```

事件按用户版本幂等应用，游标在整页成功后推进，失败可重放。`watch` 由 Supervisor/systemd 等进程管理器监督，满页立即追赶，空页定时轮询；失败指数退避且仅输出固定错误文案，不记录 SQL、邮件地址或内部凭据。SIGTERM/SIGINT 等当前页完成后退出，缺 CLI pcntl 扩展时启动失败；Local_Api/Local_App 已实际构建并验证 PHP/扩展和锁定 vendor 安装。投影仅按稳定主键修改用户，用户名/邮箱唯一键冲突会报错，不能把另一用户的业务归属覆盖。停用账号保留业务行，不级联删除业务资源。余额、积分和文件资产不从 Identity 写回。

若业务库已有 `device_sn`，须先完成其历史迁移 `m260928_150000_preserve_revoked_device_sn`，再消费用户删除事件。删除投影会在同一业务事务保留 `original_user_id` 和 UUID、清空当前绑定并禁用设备，保留永久撤销墓碑；缺该列时整页失败并不推进游标。用户业务行和内容外键仍保留，避免软删除后旧设备被重用。

维护窗口内先冻结旧账号写入，完成全量/增量和 refresh 会话最终导入，然后才启动原生 Identity HTTP。Identity 启动会把私库 `identity_migration_control.authority` 从 `staging` 单向提升为 `identity`，激活后旧导入 apply 被拒绝，避免用旧库覆盖新密码或新账号；不可先启动原生 HTTP 再补导入。

校验每个业务用户在 Identity 中存在后，才能执行以下清理。它不会在普通迁移或默认启动中自动运行：

```sh
IDENTITY_DATA_SOURCE=identity php yii identity-projection/sanitize-credentials
```

清理会将本地凭据设为不可认证占位值并移除重置/验证 token。需要确保旧服务、后台和计划任务已经停止写账号表。

最终维护窗口还应应用 `docs/sql/identity-retire-business-credentials.sql`（仅提供文件，不会自动应用）。脚本要求操作员明确确认 Identity 已成为权威；CHECK 约束禁止恢复本地密码、认证 key、重置/验证/access token，触发器禁止删除或重编号业务用户。MySQL DDL 隐式提交，因此须先冻结写入并完成导入/对账。应用和投影账号均不可拥有 DDL/TRUNCATE/TRIGGER 权限，业务账号只读用户/授权表；仅允许投影账号写用户显示字段与游标。删除/停用主体一律保留 FK 行，防止现有 CASCADE 外键删除场景内容。

## 旧账户页面与 provider

`site/*`、`entry/*` 的 HTML 登录、注册、改密、验证邮件页面保留原 controllers/views。共享表单只调用 Identity 生成/消费 token、创建用户、认领旧邀请码和分配原角色；PHP mailer 用返回的内存 DTO 发送原模板与 URL，不再在旧库写凭据。已发出的 reset token 使用源部署的实际 TTL（原默认 3600 秒），Identity 配置 `IDENTITY_LEGACY_RESET_TOKEN_TTL_SECONDS` 必须一致。

后台 `IdentityBackendUser` 在服务器 session 中保存 Identity access/refresh，每次请求验签并检查当前撤销状态，过期 access 仅向 Identity refresh，退出撤销会话。旧 Yii session 必须携原 auth_key；旧 remember-me cookie 必须先通过 Yii Request 的原签名校验，再由 Identity 对私库 auth_key/current status/revocation/cutoff 校验，不能只凭 userId 转换。`IDENTITY_LEGACY_PHP_SESSION_ACCEPT_UNTIL` 明确截止，空值关闭旧桥。转换不会重发或延长旧 identity cookie。新 remember-me 仅持久化 PHP server session cookie，不把 refresh token 发给浏览器。

旧 cookie 内容仅有 `[id, authKey, duration]`，服务端看不到浏览器原始 expiry，也无法从 duration 恢复原 issuedAt 或剩余期限。桥接保留原 cookie 原样，依赖浏览器自然过期，并遵守原 server session 的 authTimeout/absoluteAuthTimeout；操作员必须按实际最长旧 cookie TTL 设置明确 accept-until。Identity 网络故障保留服务器 token；refresh 返回后再次检查当前会话，已退出或撤销的 family 不能恢复登录。

验证邮件只消费 Identity 同事务返回的 `authTokens` 执行自动登录，不再根据返回 userId 单独发号。已用链接的幂等结果没有有效 proof 时仅显示验证成功；不会重新登录。新邮件和旧链接均沿用原模板、URL 和源部署 reset TTL。

`home/invitation`、`home/add-invitation` 和 `info/create-invitation` 已保留原 HTML 页面。Home 的 recipient/auth_item_name 邀请来自私库；Info 的手机号资料和保存的邀请显示码仍属业务，发码、每日 10 个额度及 businessInfoId 幂等由 Identity 事务完成。mdm 原用户/角色/权限/规则/路由/授权页面保留；用户列表与详情、角色用户姓名均读取私库，账号及 policy 写入都走 RPC。委托非 root 管理者时，PHP 从当前执行的真实 `/admin/controller/action` 生成 envelope `permissionName`，Identity 按当前权限图重新验证 actor 和精确路由；浏览器不能指定 actor 或扩大权限。默认 legacy 模式仍调用原父实现。

Apple 三个现有 `v1/site/apple-id*` 入口已接通：PHP 使用 Composer 锁定的 `patrickbussmann/oauth2-apple`、`league/oauth2-client` 和 `firebase/php-jwt` 完成 code exchange，再显式按 Apple JWKS 校验 RS256 签名及 issuer/audience/subject/有效期。仅向 Identity 内部接口发送已验证 provider 断言，所有账号解析、创建、密码验证、绑定和 token 签发在 Identity 私库。前端提交的 aud、redirect URL、key 环境变量名只能从 `IDENTITY_APPLE_CLIENT_IDS`、`IDENTITY_APPLE_REDIRECT_URIS`、`IDENTITY_APPLE_KEY_ENV_NAMES` 精确允许列表选择；未配置时失败。Apple 邮箱仅作已验证资料，不自动合并账号。`user.auth` 保持字符串，额外 token 元数据不改变旧字段。

旧待扫码二维码须先发布兼容基线：应用 `m261012_020000_create_legacy_wechat_scene_evidence` 并在所有实际发行节点开启 `IDENTITY_CAPTURE_LEGACY_QR_SCENES=true`。现行三个 Yii 发码入口保存 scene SHA-256、exact appId、issuedAt、expiresAt 和来源，不保存 bearer scene、openId 或伪造已扫码状态。`api/wechat/qrcode` 有效 3600 秒，site/v1 入口有效 6 天；至少经过最长 6 天后再最终 drain/export/import，才能证明所有仍有效旧 ticket 都有期限证据。未扫描 scene 不能靠缺 TTL 的 Redis key 猜测有效期。该捕获测试不连接真实 Redis。

独立模式的二维码网关保留浏览器原始 `Origin` 和 `Referer` 头。Identity 以 `IDENTITY_CORS_ORIGINS` 精确白名单验证来源，非空 Origin 优先，缺省才使用 Referer；只保存白标跳转的域名，不修改 JWT audience。PHP 拒绝控制字符或超长头，不静默丢弃非法 Origin 后转而接受 Referer。`IdentityProviderOriginHeadersTest` 包含真实本地 HTTP 透传测试。

旧邮箱验证码须在全部发行节点提前开启 `IDENTITY_CAPTURE_LEGACY_EMAIL_CHALLENGES=true`（默认关闭，仅 legacy 模式生效）。Redis 7+ Lua 同时写原 `code/created_at` payload 和独立 companion；后者只记录已认证且有效用户的 ID、当时邮箱（初次绑定为空时规范为 null）、目标邮箱、验证码 SHA-256 和原 issuedAt，并用 `PEXPIRETIME` 取得原码的确切期限，`PEXPIREAT` 保持完全相同到期时间。原响应、原 key 和 900 秒 TTL 不变。匿名覆盖、邮件失败和成功消费均清理相应关联；迟到的失败邮件不能删除更新的码。至少发布并等待完整 900 秒后再 drain/export，以原期限、码摘要及私库当时邮箱核验 association。匿名、缺证或不能证明期限的有效码不得猜测迁入；无法在既定预算内补齐证据时取消本次切换。

## 开启前仍需完成

- 验证真实 Apple/微信/邮件凭据与旧账号页面端到端请求。Apple code exchange 在测试中 mock 外部网络，真实 RSA/JWKS 验证已执行；Composer 锁已由官方 solver 生成，锁定 vendor 已在隔离 Docker PHP 环境安装；真实 provider 网络仍须目标开发环境验证。
- 在目标隔离部署完成 MDM 实际浏览器旅程和非 root 明确 route grants 验证，包括创建用户、编辑规则、授权/撤权与退出。原页面已通过 PHP 原模板渲染及定向写入测试；完整 HTTP、资源发布、CSRF、不同历史角色授权和目标浏览器 cookie 行为仍须端到端验收。`email/test` 是不读写身份的原 PHP 邮件 provider 诊断，保留其原实现；`password/verify-token` 已接当前私库 reset challenge，只读校验，不混用旧 user.password_reset_token。
- 验证现有 access、Redis refresh、待完成扫码和注册凭据的最终导入；新短码由 Identity 保存主体和 SHA-256 摘要，60 秒内允许多设备兑换，每次创建独立会话。维护前二维码/旧短码的状态导入仍需专门演练。
- 真实 MySQL 上投影插入/幂等、唯一键冲突回滚、凭据 CHECK、实际迁移生成的 verse CASCADE FK 与禁止删除/改 ID 已验证；部署数据全量对账、持续 worker 落后/故障演练及最小数据库权限仍须在目标隔离环境执行。
- 验证业务用户投影数据库权限、旧写任务停用、邮件和微信 provider 连接及前端聚合返回兼容。
- 支付接口不会被身份网关转走。`advanced/backend/controllers/WechatController.php` 的支付与回调代码整体处于注释中，当前不是可执行入口；如恢复该历史功能，须读取 Identity exact provider appId 的当前绑定，不能继续依赖主库 `wx_openid`。Identity 已提供相应内部绑定读取接口，但恢复支付必须单独集成验证。
- 完成回滚演练。默认回滚应用版本仍保持 Identity 为唯一身份权威；恢复旧库权威需要冻结写入并反向同步所有新增账号、密码、角色、绑定和撤销数据，不能直接改数据源开关。

## 验证

`IndependentIdentityBoundaryTest` 覆盖不回落、HTTP readiness、角色/全局授权、双向业务 ACL 交集、资料聚合、CLI 真实选项解析/退避、当前校园成员和组织撤销。`IdentityUserProjectionTest` 用独立 SQLite 验证业务数据保留、凭据白名单、版本幂等、唯一键不覆盖其他用户及旧二维码期限证据。`IdentityUserProjectionMysqlTest` 默认跳过外部 DB，显式 `IDENTITY_TEST_MYSQL=1` 才创建随机库并用真实 Yii 迁移、真实投影和退役 SQL 校验，结束删除随机库。

`IdentityLegacyAdminUiTest` 使用锁定 mdmsoft 2.12 和 HTMLPurifier，执行原 GridView/DetailView/邀请模板及原角色创建 action，覆盖 private 用户/角色/授权、分页、Home/Info 和服务端 route envelope。只禁用外部 JS/CSS 资源发布，不替换 HTML widget。`IdentityBackendUserTest` 覆盖真实 Yii 签名 cookie、伪造 cookie、旧 server session、503 保留 tokens、refresh 与退出交错；`IdentityLegacyFormsTest` 覆盖旧邮件模板和验证 proof 消费。`IdentityPolicyGraphTest` 验证当前远端 policy 与 PHP 对象 rule 交集、直接授权及远端 mutation。

`IdentityLegacyEmailAssociationTest` 仅在 `IDENTITY_TEST_REDIS=1` 时连接显式配置的独立 Redis（默认 127.0.0.1:16379），用随机 key 验证真实 Lua、精确期限、初次绑定、匿名覆盖、失败邮件竞态和消费清理；只删除自身测试 key，不执行 FLUSH。

```sh
cd advanced
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/IndependentIdentityBoundaryTest.php
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/IdentityUserProjectionTest.php
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/AppleIdentityProviderSignatureTest.php
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/IdentityLegacyFormsTest.php
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/LegacyWechatGatewayTest.php
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/IdentityBackendUserTest.php
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/IdentityPolicyGraphTest.php
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/IdentityLegacyAdminUiTest.php
IDENTITY_TEST_MYSQL=1 php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/IdentityUserProjectionMysqlTest.php
IDENTITY_TEST_REDIS=1 IDENTITY_TEST_REDIS_PORT=16379 php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/services/IdentityLegacyEmailAssociationTest.php
php vendor/bin/phpunit --bootstrap test_bootstrap.php tests/unit/contracts/EmailDeliveryFailureContractTest.php
```

当前主机无原生 PHP，通过临时 PHP 8.4.25 WASM、锁定版本 Yii2/bizley/Firebase7.2.1 和 PHPUnit 10.5.63 实际运行上述 112 项测试、357 项断言，79 个改动 PHP 文件通过语法解析。MySQL 测试使用仅测试环境的 PDO transport bridge 连接临时 MySQL8.4 实例；SQL、事务、真实迁移及生产投影代码均实际执行，不是 mock 数据库结果。邮箱 companion 测试以测试专用 Redis transport bridge 执行临时官方 Redis 7.4.2 的真实命令和 Lua，结束关闭该实例，没有连接源环境 Redis。标准 `make test` 依赖完整项目容器/扩展，尚未在此环境执行。

整合 GitHub develop `7e905338` 的 SN、WebMCP 与 Task51 更新后，Docker PHP 8.5.9 / PHPUnit 12.5.4 定向运行 436 项测试、3072 个断言通过；14 项因未启用外部 MySQL/Redis 测试跳过，原 RateLimit mock 有一个 PHPUnit notice。另以本机一次性 MySQL 显式运行投影测试 6 项、35 个断言通过，包含实际资源 FK 保留、SN 永久撤销墓碑及缺墓碑迁移时不推进版本；没有连接源环境或线上数据库。完整单元集仍依赖其原数据库/Redis fixture，不能将该定向结果称为全部业务测试通过。

CI 手动 `workflow_dispatch` 的 `publish_candidate` 默认为 false。显式开启或仅推送指定整合分支 `codex/identity-online-dev-20261012` 时，在测试通过后将同一双 VirtualHost Release 镜像发布到 `api:candidate-<full-sha>` 和 `app:candidate-<full-sha>`，固定 linux/amd64、OCI revision 并记录 registry digest；不会覆盖 develop/main/publish/latest，也不执行部署。既有 push 分支发布条件保持不变。

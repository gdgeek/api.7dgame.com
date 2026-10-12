# SN 分发管理与 y1 设备认证边界

**2026-09-29 本地改造，尚未部署开发或生产。** 网页管理仍由主 API 提供；Unity/Rokid
的 SN 激活、登录、刷新和退出改由 `backend/yii3-a1`（y1）提供，复用 y1 原用户名密码
登录的 HS256 Token 协议。此前“主 API + identity”发布记录属于历史版本，不能视为
本次 y1 或账号删除作废改动已经上线。

## 行为

获得当前插件配置授权的管理者通过 `sn-management` 插件选择已有普通账号，一次生成 1–100 个永久 SN。
默认注册配置为 `root-only`；root 可在系统管理插件中改为 `admin-only`，允许 root/admin 分发，
也可以再改回 `root-only`。分发操作者的权限与 SN 绑定账号的资格分别检查。
每个 SN 只属于一个账号，首次激活只绑定一个 UUID；同账号可绑定多台设备。
管理端可以停用/恢复及修改备注，不能解绑、换机、改账号或直接删除 SN。
删除绑定用户账号会使其全部 SN 永久作废，保留记录、原账号 ID、设备 UUID 和已有审计；
作废后不能恢复，重新创建同名或同 ID 账号也不能复活原 SN。账号暂时停用不触发永久作废。
仅 `status=10` 且无 `root/admin/manager` 角色的账号可以使用设备会话。

设备 UUID 直接存入 `device_sn.device_uuid`，该字段可为空并具有唯一约束。生成 SN 时为空，
首次激活时在事务内写入；同一 SN+UUID 重复激活幂等，其他 SN 不能占用该 UUID。
停用不会清空 UUID，恢复仍只允许原设备登录。UUID 唯一性只限于 SN 系统：旧 `device` 表
继续保留，但本功能不读写它，也不使用旧设备的 `owner_id` 或 `active` 判定归属及授权。
因此，UUID 仅在旧设备表中存在或归属其他账号，不构成 SN 激活冲突。

UUID 支持 1–255 个 ASCII 字母、数字、点、下划线、冒号、连字符，
首字符必须为字母或数字；去除首尾空白并统一小写。Rokid 应从稳定的设备标识 API
读取 UUID，不能每次启动随机生成。它是客户端声明的标识，不构成硬件防克隆证明。

新生成 SN 为 16 位随机 Crockford Base32 字符，80 bit 熵，每 4 位分组；显示为
`0000-1111-2222-3333` 这样的 4 组格式，含连字符共 19 个字符（示例不是有效凭据）。
新 y1 设备接口和 Unity 客户端只接受规范化后的 16 位码，忽略大小写、ASCII 空白和连字符，
不猜测 `I/L/O/U` 等字符。主 API 的存储、查看和导出仍兼容 32 位历史码，保留其 160 bit 熵
和 8 组显示格式，不截断、不补齐、不重新发行；这不表示 y1 接受历史 32 位输入。
数据库保存 SHA-256 查询摘要及 AES-256-GCM 密文，完整 SN 只在受授权的生成、查看、
导出响应中出现。审计复用 `audit_log`，resource 为 `device_sn/{id}`，不记录完整 SN。

## 本地实现：Unity / Rokid 认证迁至 y1

设备使用同环境 y1 的接口：

| 方法和路径 | 用途 |
|---|---|
| POST `/v1/auth/sn-activate` | 首次绑定 UUID 并登录；同一对凭据可幂等重试 |
| POST `/v1/auth/sn-login` | 已激活设备以相同 SN + UUID 登录，不隐式激活 |
| POST `/v1/auth/refresh` | 轮换 y1 Refresh Token，持续核验并保留 SN 来源 |
| POST `/v2/auth/refresh-token` | 严格刷新入口，同样保留 SN 来源 |
| POST `/v1/auth/logout` | 撤销指定 y1 Refresh Token，保留 SN / UUID 绑定 |

激活、登录请求体仍是 `{sn,uuid}`，成功仍含
`{success:true,message:"login",token:{accessToken,expires,refreshToken}}`，并返回安全的账号信息。
y1 使用自己的 `JWT_KEY`（HS256），不调用 identity 内部签发接口，不接受主 API 的 EC Token
作为自身登录态。Unity 的旧主 API 会话不能直接变为 y1 会话，必须用原 SN + UUID 向 y1
重新登录；已共享的绑定不用复制或重建。接口详情见超级项目
`backend/yii3-a1/docs/device-sn.md` 和 `docs/sn-management/UNITY_CLIENT_README.md`。

主 API 的 `POST /v1/auth/sn-activate` 与 `POST /v1/auth/sn-login` 在本次版本明确返回
`410 Gone`，消息提示改用本环境 y1。保留的 action 和显式路由仅负责返回 410，
默认 Yii controller/action 路由也不能继续激活或签发；错误体不包含跳转域名、SN 或 Token。
主 API 不重定向、不代理这些凭据，客户端必须使用已核验的 y1 环境配置。
主 API 现有用户名密码登录、刷新和退出不改协议；此前已签发主 API SN 会话的 refresh
仍按原有来源校验处理，不能去掉来源以转成普通登录。新 Unity 会话只使用 y1 刷新和退出。

只有停用 SN 时，新登录和刷新立即拒绝，已有 Access 最多继续 3 小时；恢复仍限原 UUID。
删除绑定账号使共享表 `user_id=NULL`，永久阻止激活、登录、刷新及后续 Access 鉴权，
包括同 ID 重建账号；已经通过授权校验的在途请求可能完成。账号暂时停用或升权会拒绝
SN 会话，但不产生永久作废墓碑。设备会话不能派生不受 SN 限制的二维码/OIDC 等凭据。

## 管理 API

所有 `/v1/plugin-sn` 接口都要求正常启用账号的 Bearer Token，返回 `Cache-Control: no-store`。
`GET /v1/plugin-sn/access` 仅查询当前能力；其他管理接口在每次请求时重新读取权威配置，
根据 `plugins.access_scope` 和该账号当前真实角色决定是否放行。`root-only` 允许 root，
`admin-only` 允许 root/admin，`manager-only` 再允许 manager，`auth-only` 再允许普通 user。
SN 来源的 Token 始终不能管理 SN，即使插件被配置为 `auth-only`，也不能借此派生新凭据。
这些限制不改变“SN 仅能绑定普通启用账号”的规则。

权限切回 `root-only` 后，admin 的下一次请求立即拒绝；旧 Token、已打开页面或之前查询的
能力结果不会保留管理资格。修改前已通过检查的在途请求可能完成。插件禁用、不存在或属于
私有组织时任何角色都拒绝，包括 root；配置读取失败、版本/响应不符合协议或 scope 非法时
返回 `503`，不使用缓存许可，也不回退为 `auth-only` 或固定 root 放行。默认 `root-only`
必须写在插件登记配置中，缺失 scope 不自动授予任何权限。

策略源不可用的 `503` 保留 Yii 的 `name/message/code/status` 字段（`code` 仍为整数 `0`），
另增加稳定字段 `error_code:"PLUGIN_ACCESS_CONFIG_UNAVAILABLE"`。插件对任一 SN 管理请求
收到 `503` 且该 `error_code` 精确匹配时立即撤销本地能力、清除 Token 与敏感页面结果，并
使已在途响应失效；普通业务 `503` 不附带该码，不能仅凭 HTTP 状态区分两种情况。
列表和详情只返回尾号，不返回摘要、密文、密钥或明文 SN。
列表、详情、生成与导出中的设备字段仍为 `device_uuid`：未激活时为 `null`，激活后为
规范化 UUID，停用或作废后保持原值。删除账号后 `user_id/username/nickname` 为 `null`，
`original_user_id` 保留原账号 ID，`enabled=false`、`status=revoked`，
`revocation_reason=account_deleted`；其他状态的 `revocation_reason` 为 `null`。
`original_user_id` 仅用于展示和历史筛选，绝不能作为认证绑定。

| 方法和路径 | 输入/结果 |
|---|---|
| GET `/v1/plugin-sn/access` | `{success:true,data:{allowed:boolean,access_scope:合法scope或null}}`；已认证但无权限仍为 200，配置源不可用为 503 |
| GET `/v1/plugin-sn/accounts` | `q,page,page_size`；仅返回可绑定账号 `id,username,nickname` |
| GET `/v1/plugin-sn` | `q` 搜索尾号/账号/UUID；`status=pending/active/disabled/revoked`、`user_id`、分页 |
| GET `/v1/plugin-sn/{id}` | 脱敏详情及最近 100 条操作审计 |
| POST `/v1/plugin-sn/generate` | `{user_id,count:1..100,remark}`；201，`data.items` 含本批完整 `sn` |
| PATCH `/v1/plugin-sn/{id}` | 只接受 `{enabled?:boolean,remark?:string}`，备注最多 500 字；已作废 SN 只可改备注，提交 `enabled` 返回 409 |
| POST `/v1/plugin-sn/{id}/reveal` | `data:{id,sn}`，记录查看审计 |
| POST `/v1/plugin-sn/export` | `{ids:[整数]}`，最多 100 条，`data.items` 含完整码并逐条审计 |

分页响应是 `{success:true,data:{items,total,page,page_size}}`，默认每页 20，最大 100。
其他管理响应是 `{success:true,data:...}`；错误使用 Yii 标准 HTTP 状态及 `message`。
SN 时间字段采用 UTC 数据库时间。`user_id` 筛选也匹配已删除账号的 `original_user_id`；
删除账号后不保留用户名快照，因此账号名搜索不能找回已删除账号的 SN。
作废记录仍允许查看详情、完整码、导出及改备注，用于留档；凭据不再能登录。

### 管理授权的配置来源

主后端通过显式环境变量 `PLUGIN_ACCESS_CONFIG_BASE_URL` 读取 system-admin 狭窄配置接口，
例如开发 `http://system-admin-d:8088`、生产 `http://system-admin-p:8088`；实际服务名须与
对应部署网络一致，不从请求、iframe INIT 或主库名称推导地址。仅拼接固定路径
`/api/v1/plugin/access-config/sn-management`，不转发用户 Token、Cookie、Host 或角色。
读取仅使用 HTTP(S)，禁用代理与重定向，连接超时 1 秒、总超时 5 秒、响应上限 16 KiB。
每次实时读取，无许可缓存；Apache 配置已为该变量增加 `PassEnv`。

配置端只返回 `organization_name IS NULL` 的公共插件元数据，不再回调主 API 验证 Bearer，
避免 API → system-admin → API 同步依赖占满请求进程。禁用、不存在或组织非 NULL
（包括空白字符串）返回 `404`，主后端转为 `allowed:false,access_scope:null`。
私有组织插件本轮不支持，不能假设 root 可以越过这一限制。
成功响应必须为 `code:0`，且 `data` 包含 `policy_version:1`、匹配的插件 `id`、
`enabled:true` 和严格合法的 `access_scope`；主后端独立比较当前用户角色。
配置接口不读取/复制主 API 数据库凭据，不返回插件 URL、组织名或其他配置字段。

先部署提供此严格接口的 system-admin 后端，再给两侧主 API 设置对应环境地址并升级，
最后部署使用 `/v1/plugin-sn/access` 的插件前端。双侧配置服务必须读取同一个本环境的
权威插件配置库，否则不同 API 可能给出不同的权限判断。此授权调整不新增表、不迁移
SN/用户数据，也不重新启用旧 `allowed-actions/check-permission` 插件内部路径。

## 配置、迁移和双后端

1. 在主库执行 Yii migration `m260926_210000_create_device_sn_table`。前置条件为已有
   `user/audit_log/auth_item/auth_item_child` 表及 root 角色，不依赖旧 `device` 表。
   迁移创建包含 nullable unique `device_uuid` 的 `device_sn`，同时登记管理路由。
   接着执行增量迁移 `m260928_150000_preserve_revoked_device_sn`：新增并回填
   `original_user_id`，将 `user_id` 改为可空、外键改为 `ON DELETE SET NULL`。
   仍只使用原 `device_sn` 表；账号删除与作废由数据库原子完成，覆盖各删除入口。
2. 配置 `DEVICE_SN_ACTIVE_KEY_ID` 与 `DEVICE_SN_KEYS`。后者是 key ID 到 base64 编码
   32-byte AES key 的 JSON。用 `openssl rand -base64 32` 生成随机密钥，通过部署 secret
   注入，例如 `{"v1":"<base64 key>"}`；不要提交真实值。更换 active key 后保留旧 key
   供历史 SN 解密。缺少密钥时生成/查看/导出失败，不退化成明文保存。
3. 主 API 的 `deviceSnDb` 使用同环境主库 MYSQL_* 和标准 Yii Connection，关闭副本读，
   不使用 CynosDB 逐语句重试。y1 的 `MYSQL_HOST/MYSQL_DB/MYSQL_USER/MYSQL_PASS`
   必须连接同一权威主库；共享原表，不复制 SN 绑定，不从异步副本决定授权。
4. 主 API / identity 的既有 `AUTH_PROVIDER`、EC 验签和历史 SN 会话配置保持不变；
   identity 的 LEGACY_DB_* 继续读取同一主库，已有 session 来源列不能移除。它们只负责
   原有网页或历史会话兼容，不作为 y1 签发依赖。y1 不需要设置 identity internal token、
   token issuance 开关或主 API AES keyring。主 API 两侧 AES keyring 一致；y1 各节点使用
   同一组现有 HS256 `JWT_KEY`，两种签名体系不可混用。
5. y1 的 `REDIS_HOST/REDIS_PORT/REDIS_DB` 必须指向本环境同一权威 Redis 逻辑库，
   各节点共享刷新会话及 SN 限流状态；Redis 故障时设备认证拒绝请求。限流使用 SN/UUID
   摘要，详细行为见 y1 文档。保留主 API 的内部历史会话检查不代表重新公开设备登录入口。
6. 插件仍连接主 API，Unity 改连 y1；生产插件 Nginx 使用 `APP_API_1_URL`，不能将其
   改成 y1。y1 对外环境地址须按部署核验；未证明节点一致性前固定到一个已确认入口。
   业务入口不是 Portainer 管理入口。

此前 16 位生成及 32 位历史码留档兼容已有发布记录；本次 y1 接入只接受 16 位，
部署前需核对客户端现有凭据，不得将历史 32 位码截断后使用。转移认证职责无需新表或
重新绑定；账号删除永久作废仍须先完成上面的既有表增量迁移。所有新代码的部署状态
必须按目标环境镜像和本轮功能验收确认，历史发布记录不能代替。
回滚应先关闭插件和设备登录入口、停止 SN 发行，再部署兼容版本；已有 SN 后不要
直接执行破坏性 `safeDown`。数据库和加密
keyring 应配套备份，丢失旧 key 会导致历史 SN 无法再次查看，但摘要验证仍可用。

### 限定 SN 迁移白名单

在 Portainer 中选择已更新到本次镜像的主 API 容器，打开 `/bin/sh` 控制台。
先确认该容器的 `MYSQL_HOST/MYSQL_DB` 对应目标环境的权威写库，已有迁移历史表、
`user/audit_log/auth_item/auth_item_child` 表及 `root` 角色。首次安装按顺序执行建表和保留作废记录
两项迁移；已有环境应已有建表迁移历史，只会执行新的增量迁移。若表已存在但没有建表迁移
历史，先检查此前执行是否中途失败，不要直接重跑。迁移及升级主 API 期间暂停账号删除和 SN 生成。
共享同一主库的双后端只需在一侧执行一次。

以下使用临时目录限定迁移候选，既不会执行其他未完成迁移，也不会执行 Task 5.1 的迁移命令。
`task51CoordinatorDb` 在现有 console 配置中是标准、无重试的 Yii 数据库连接，
使用同一组 `MYSQL_*`，这里仅复用这个连接执行 SN DDL：

```sh
set -eu
cd /var/www/html/advanced
sn_create=m260926_210000_create_device_sn_table.php
sn_revoke=m260928_150000_preserve_revoked_device_sn.php
test -f "console/migrations/$sn_create"
test -f "console/migrations/$sn_revoke"
sn_migration_dir=$(mktemp -d /tmp/device-sn-migration.XXXXXX)
trap 'rm -f "$sn_migration_dir/$sn_create" "$sn_migration_dir/$sn_revoke"; rmdir "$sn_migration_dir"' EXIT
cp "console/migrations/$sn_create" "console/migrations/$sn_revoke" "$sn_migration_dir/"
php yii migrate/new --db=task51CoordinatorDb --migrationPath="$sn_migration_dir" --interactive=0
php yii migrate/up 2 --db=task51CoordinatorDb --migrationPath="$sn_migration_dir" --interactive=0
php yii migrate/new --db=task51CoordinatorDb --migrationPath="$sn_migration_dir" --interactive=0
```

首次安装最多应用这两项 SN 迁移，已有环境仅应用尚未执行的项，最后一步应无待执行迁移；
成功后重复运行会根据迁移历史跳过。MySQL DDL 不是整个迁移原子回滚，若执行失败应先核对
`device_sn`、索引、外键和 RBAC 状态，再决定修复步骤。新的迁移可从已加列或已完成外键
替换的状态重试；完成后核对 `user_id` 可空、`original_user_id` 已回填，且
`fk_device_sn_user_retained` 为 `ON DELETE SET NULL`。

GitHub CI 的 `Run Migrations` 会在临时 `yii2_advanced_test` 数据库执行应用迁移，
因此会包含本迁移；这不等于部署环境已迁移。`docker/Release` 只构建应用镜像，
没有自动运行迁移的启动命令，Portainer 更新容器后仍须执行上述单次增量步骤。

### 账号删除永久作废的升级要求

本项为本地实现，尚未发布到开发或生产环境；此前发布记录不代表该增量迁移已执行。
升级期间暂停所有账号删除入口及 SN 生成，备份数据库，在同一权威库执行
`m260928_150000_preserve_revoked_device_sn`，随后升级全部主 API 和参与设备鉴权的 y1 节点，再升级插件并恢复操作。
暂停删除避免旧外键仍执行级联删除；暂停生成避免旧后端在回填后生成缺少原账号 ID 的记录。
只需对双后端共享的权威库迁移一次，不新建表，不依赖旧 `device` 表。

`user_id=NULL` 是不可恢复的作废状态；UUID 唯一占用继续保留，不能发新码给同 UUID 换绑。
已被旧 `CASCADE` 规则删除的历史 SN 无法通过此迁移补回。迁移不提供破坏性回退；回滚应用
时也须保留 nullable 外键与作废记录，并使用支持作废状态的兼容版本。

## 验证

```sh
cd advanced
php vendor/bin/phpunit --do-not-cache-result -c phpunit.xml --filter DeviceSn
# 仅一次性测试 MySQL，脚本会创建并删除随机命名的 codex_device_sn_php_* 测试库：
DEVICE_SN_MYSQL_TEST_PORT=13318 php tests/integration/device_sn_mysql.php
# 同时验证真实 Redis/JWT 与密码登录（Redis 也必须是一次性测试容器）：
DEVICE_SN_MYSQL_TEST_PORT=13318 DEVICE_SN_REDIS_TEST_PORT=16318 php tests/integration/device_sn_mysql.php
```

上线验收记录应包含环境、镜像/提交、迁移及 key ID（不含 key 内容）、测试用例和结果。
验证主 API 管理页分发→y1 激活→重登→两种刷新→退出，以及主 API 两个旧入口均410。
再验证单码停用、另一设备和密码登录不受影响；分别回归 y1 普通密码会话与主 API 历史 SN 刷新。
额外使用专用测试账号验证删除后 SN 留档且无法恢复、激活、登录、刷新或使用旧 Access；
重建同 ID 账号不能复活原码；回滚删除事务不影响原 SN，作废设备 UUID 不能被新码占用。
双后端必须额外验证 A 激活/B 登录、A 停用/B 拒绝刷新、并发绑定及失败切换。

# SN 分发与 Rokid 登录

## 行为

root 通过 `sn-management` 插件选择已有普通账号，一次生成 1–100 个永久 SN。
每个 SN 只属于一个账号，首次激活只绑定一个 UUID；同账号可绑定多台设备。
管理端可以停用/恢复及修改备注，不能解绑、换机、改账号或直接删除 SN。
仅 `status=10` 且无 `root/admin/manager` 角色的账号可以使用设备会话。

设备 UUID 直接存入 `device_sn.device_uuid`，该字段可为空并具有唯一约束。生成 SN 时为空，
首次激活时在事务内写入；同一 SN+UUID 重复激活幂等，其他 SN 不能占用该 UUID。
停用不会清空 UUID，恢复仍只允许原设备登录。UUID 唯一性只限于 SN 系统：旧 `device` 表
继续保留，但本功能不读写它，也不使用旧设备的 `owner_id` 或 `active` 判定归属及授权。
因此，UUID 仅在旧设备表中存在或归属其他账号，不构成 SN 激活冲突。

UUID 支持 1–255 个 ASCII 字母、数字、点、下划线、冒号、连字符，
首字符必须为字母或数字；去除首尾空白并统一小写。Rokid 应从稳定的设备标识 API
读取 UUID，不能每次启动随机生成。它是客户端声明的标识，不构成硬件防克隆证明。

SN 为 32 位随机 Crockford Base32 字符，160 bit 熵，每 4 位分组。
输入忽略大小写、空白、连字符；不自动猜测 `I/L/O/U` 等字符。
数据库保存 SHA-256 查询摘要及 AES-256-GCM 密文，完整 SN 只在受授权的生成、查看、
导出响应中出现。审计复用 `audit_log`，resource 为 `device_sn/{id}`，不记录完整 SN。

## Rokid 接入

主后端原生路径是 `/v1/...`；经主站/插件代理时一般是 `/api/v1/...`。
客户端配置环境的同一个权威 API base URL，生产使用 HTTPS。

首次输入 SN：

```http
POST /v1/auth/sn-activate
Content-Type: application/json

{"sn":"分发得到的 SN","uuid":"稳定的设备 UUID"}
```

之后每次启动或 Access Token 到期：

```http
POST /v1/auth/sn-login
Content-Type: application/json

{"sn":"本机保存的 SN","uuid":"同一个设备 UUID"}
```

两者成功均返回原登录协议：

```json
{
  "success": true,
  "message": "login",
  "token": {
    "accessToken": "JWT",
    "expires": "沿用当前 issuer 的时间格式",
    "refreshToken": "原有格式的刷新凭据"
  }
}
```

后续业务调用继续使用 `Authorization: Bearer <accessToken>`；账号 ID、角色及内容权限
仍由原系统决定。客户端可以继续使用 `/v1/auth/refresh`，但 Rokid 的默认策略为
启动/到期时以 UUID+SN 重登。不要在 URL、遥测、崩溃报告或截图中暴露 SN/Token；
SN 存入平台安全存储，退出设备授权时清除本机凭据。不要在业务请求失败后无限重复登录。

- 400：输入格式不正确，提示用户检查 SN/UUID。
- 401：SN 停用、账号失效或授权无效；停止自动重试并提示联系管理员。
- 409：需要首次激活或已有冲突绑定；未激活设备走激活入口，冲突不得自动换绑。
- 429：遵循 `Retry-After`。
- 5xx/网络异常：1、2、4、8、16、最多 30 秒指数退避并加入随机抖动。

同一 SN+UUID 重复激活幂等；激活提交后即使签发失败或响应丢失，仍可用相同凭据重试。
停用提交后开始的新登录和刷新都拒绝；已通过授权检查的在途请求可能完成，既有
Access Token 最多继续 3 小时。恢复只恢复原绑定。账号删除、停用或升为管理员会拒绝
该账号的 SN 会话。SN 会话不允许生成二维码登录码、OIDC 换票或修改账号密码/邮箱。

## 管理 API

所有 `/v1/plugin-sn` 接口都要求正常启用的 root 用户 Bearer Token，返回 `Cache-Control: no-store`。
列表和详情只返回尾号，不返回摘要、密文、密钥或明文 SN。
列表、详情、生成与导出中的设备字段仍为 `device_uuid`：未激活时为 `null`，激活后为
规范化 UUID，停用后保持原值。后端存储调整不改变插件的 API 字段或 TypeScript 类型。

| 方法和路径 | 输入/结果 |
|---|---|
| GET `/v1/plugin-sn/accounts` | `q,page,page_size`；仅返回可绑定账号 `id,username,nickname` |
| GET `/v1/plugin-sn` | `q` 搜索尾号/账号/UUID；`status=pending/active/disabled`、`user_id`、分页 |
| GET `/v1/plugin-sn/{id}` | 脱敏详情及最近 100 条操作审计 |
| POST `/v1/plugin-sn/generate` | `{user_id,count:1..100,remark}`；201，`data.items` 含本批完整 `sn` |
| PATCH `/v1/plugin-sn/{id}` | 只接受 `{enabled?:boolean,remark?:string}`，备注最多 500 字 |
| POST `/v1/plugin-sn/{id}/reveal` | `data:{id,sn}`，记录查看审计 |
| POST `/v1/plugin-sn/export` | `{ids:[整数]}`，最多 100 条，`data.items` 含完整码并逐条审计 |

分页响应是 `{success:true,data:{items,total,page,page_size}}`，默认每页 20，最大 100。
其他管理响应是 `{success:true,data:...}`；错误使用 Yii 标准 HTTP 状态及 `message`。
SN 时间字段采用 UTC 数据库时间。账号删除时其 SN 级联撤销，既有审计保留。

## 配置、迁移和双后端

1. 在主库执行 Yii migration `m260926_210000_create_device_sn_table`。前置条件为已有
   `user/audit_log/auth_item/auth_item_child` 表及 root 角色，不依赖旧 `device` 表。
   迁移创建包含 nullable unique `device_uuid` 的 `device_sn`，同时登记管理路由。
2. 配置 `DEVICE_SN_ACTIVE_KEY_ID` 与 `DEVICE_SN_KEYS`。后者是 key ID 到 base64 编码
   32-byte AES key 的 JSON。用 `openssl rand -base64 32` 生成随机密钥，通过部署 secret
   注入，例如 `{"v1":"<base64 key>"}`；不要提交真实值。更换 active key 后保留旧 key
   供历史 SN 解密。缺少密钥时生成/查看/导出失败，不退化成明文保存。
3. `deviceSnDb` 使用与本环境主库相同的 MYSQL_* 配置及标准 Yii Connection；关闭副本读，
   不使用 CynosDB 的逐语句重试。两个业务后端、identity 的 LEGACY_DB_* 必须指向同一
   权威写库，JWT 验签配置和 keyring 必须一致；独立库/异步复制不满足此约束。
4. 若 `AUTH_PROVIDER=identity`，先执行 identity session 增量迁移并升级签发服务，详见
   超级项目 `services/identity-service/docs/runbooks/device-sn-sessions.md`。确认其
   `/internal/auth/device-sn/readiness` 三项均 true。旧 issuer 丢弃来源时主后端拒绝发证，
   SN 失败不会回退成普通会话。普通密码会话仍按原配置运行。
5. Redis 原子限流组件 `deviceSnRateLimiter` 默认每 IP 600 次/分钟、每 SN 摘要和每 UUID
   摘要各 30 次/分钟；Redis 故障拒绝请求。双后端应共享限流状态。
6. 未证明双侧一致性前，插件和 Rokid 固定到同一个权威业务入口。插件生产 Nginx 只接受
   一个 `APP_API_1_URL`；不能用随机双后端模板。业务入口不是 Portainer 管理入口。

这是尚未部署的新功能，直接修改原新增迁移，不新增从 `device_id` 转换 UUID 的迁移，
也没有既有线上 SN 数据需要回填。旧 `device` 表及普通登录数据保持原状。
未来上线后的回滚应先关闭插件和设备登录入口、停止 SN
发行，部署兼容版本；生产已有 SN 后不要直接执行破坏性 `safeDown`。数据库和加密
keyring 应配套备份，丢失旧 key 会导致历史 SN 无法再次查看，但摘要验证仍可用。

### 已有环境只执行本次迁移

在 Portainer 中选择已更新到本次镜像的主 API 容器，打开 `/bin/sh` 控制台。
先确认该容器的 `MYSQL_HOST/MYSQL_DB` 对应目标环境的权威写库，已有迁移历史表、
`user/audit_log/auth_item/auth_item_child` 表及 `root` 角色。首次执行前应不存在
`device_sn`；若表已存在但历史中没有本次迁移，先检查此前执行是否中途失败，不要直接重跑。
共享同一主库的双后端只需在一侧执行一次。

以下使用临时目录限定迁移候选，既不会执行其他未完成迁移，也不会执行 Task 5.1 的迁移命令。
`task51CoordinatorDb` 在现有 console 配置中是标准、无重试的 Yii 数据库连接，
使用同一组 `MYSQL_*`，这里仅复用这个连接执行 SN DDL：

```sh
set -eu
cd /var/www/html/advanced
sn_migration_file=m260926_210000_create_device_sn_table.php
test -f "console/migrations/$sn_migration_file"
sn_migration_dir=$(mktemp -d /tmp/device-sn-migration.XXXXXX)
trap 'rm -f "$sn_migration_dir/$sn_migration_file"; rmdir "$sn_migration_dir"' EXIT
cp "console/migrations/$sn_migration_file" "$sn_migration_dir/"
php yii migrate/new --db=task51CoordinatorDb --migrationPath="$sn_migration_dir" --interactive=0
php yii migrate/up 1 --db=task51CoordinatorDb --migrationPath="$sn_migration_dir" --interactive=0
php yii migrate/new --db=task51CoordinatorDb --migrationPath="$sn_migration_dir" --interactive=0
```

首次执行中间步骤应报告仅应用 `m260926_210000_create_device_sn_table`，最后一步应无待执行迁移；
成功后重复运行会根据迁移历史跳过。MySQL DDL 不是整个迁移原子回滚，若执行失败应先核对
`device_sn`、索引、外键和 RBAC 状态，再决定修复步骤。

GitHub CI 的 `Run Migrations` 会在临时 `yii2_advanced_test` 数据库执行应用迁移，
因此会包含本迁移；这不等于部署环境已迁移。`docker/Release` 只构建应用镜像，
没有自动运行迁移的启动命令，Portainer 更新容器后仍须执行上述单次增量步骤。

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
验证 root 生成→Rokid 激活→重登→刷新→单码停用，以及另一个设备和密码登录不受影响。
双后端必须额外验证 A 激活/B 登录、A 停用/B 拒绝刷新、并发绑定及失败切换。

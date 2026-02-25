# 项目概览（api.7dgame.com）

更新时间：2026-02-25

## 1. 项目定位

该项目是一个基于 **Yii2 Advanced** 的后端系统，主要提供 AR 创作平台相关的 RESTful API 服务，包含用户认证、资源管理、教育/班级/群组等业务能力，并提供 Swagger/OpenAPI 文档入口。

## 2. 技术栈与依赖

- 语言与框架：PHP 8.4+、Yii2 `~2.0.51`
- 数据与缓存：MySQL 8、Redis
- 认证与安全：JWT（`bizley/jwt` + `lcobucci/jwt`）、RBAC
- API 文档：`zircote/swagger-php`
- 第三方集成：微信（`w7corp/easywechat`）、腾讯云 STS/COS
- 测试：PHPUnit、Codeception

关键依赖定义见：`advanced/composer.json`

## 3. 目录结构（核心）

- `advanced/`：Yii2 Advanced 主体代码
- `advanced/api/`：API 应用（入口、控制器、v1 模块）
- `advanced/common/`：公共配置与通用组件
- `files/`：各应用的本地配置映射目录（api/backend/common/console）
- `docker/`：Docker 镜像与部署配置
- `scripts/`：脚本（docker/email/ci）
- `docs/`：项目文档（API、Docker、安全、邮件等）

## 4. API 结构与主要模块

API 主模块：`advanced/api/config/main.php` 注册了 `v1` 模块，启用严格路由与 REST 规则。

主要控制器（`advanced/api/modules/v1/controllers`）：

- 认证与账号：`Auth`、`User`、`Password`、`Email`、`Wechat`
- 文件与资源：`Upload`、`File`、`Meta`、`Prefab`、`Resource`、`Snapshot`
- 内容与标签：`Verse`、`VerseTags`、`VerseScript`、`Tags`
- 教育与群组：`EduSchool`、`EduTeacher`、`EduStudent`、`EduClass`、`Group`、`GroupVerse`
- 云与系统：`TencentCloud`、`ScenePackage`、`System`、`Domain`、`Tools`

辅助入口控制器（`advanced/api/controllers`）：

- `Health`（健康检查）
- `Swagger`（文档入口）
- `Site`、`Server`、`Wechat`、`File`、`Resource`

## 5. 配置与环境变量

### 5.1 运行配置来源

- 通用配置：`advanced/common/config/*.php`
- API 配置：`advanced/api/config/*.php`
- Docker 环境变量模板：`.env.docker.example`
- Docker 启动文件：`docker-compose.yml`

### 5.2 关键环境变量（节选）

- DB：`MYSQL_HOST`、`MYSQL_DB`、`MYSQL_USERNAME`、`MYSQL_PASSWORD`
- Redis：`REDIS_HOST`、`REDIS_PORT`、`REDIS_DB`
- JWT：`JWT_KEY`
- 腾讯云：`SECRET_ID`、`SECRET_KEY`、COS Bucket/Region
- 邮件：`MAILER_USERNAME`、`MAILER_PASSWORD`
- Swagger 访问控制：`SWAGGER_USERNAME`、`SWAGGER_PASSWORD`、`SWAGGER_ENABLED`

## 6. 本地运行与常用命令

### 6.1 推荐方式（Docker）

```bash
./scripts/docker/start-docker.sh
```

启动后默认端口：

- API: `http://localhost:8081`
- 后台：`http://localhost:8082`
- phpMyAdmin：`http://localhost:8080`

### 6.2 Makefile 常用命令

- `make logs` / `make logs-api`
- `make migrate`
- `make rbac`
- `make test`
- `make shell`

## 7. 测试与 CI

- 本地测试命令（容器内）：`vendor/bin/codecept run`、`phpunit`
- CI 工作流：`.github/workflows/ci.yml`
  - 阶段1：安装依赖、准备测试配置、迁移数据库、执行 PHPUnit
  - 阶段2：构建并推送 Docker 镜像（push 事件）

## 8. 文档入口

- 根文档：`README.md`
- 文档索引：`docs/README.md`
- Docker 文档：`docker/README.zh-CN.md`
- 脚本索引：`scripts/README.md`

## 9. 当前可见注意事项

1. `docker-compose.yml` 中存在本地示例凭据（如 MySQL、邮件密码示例）；发布前建议全部改为环境变量注入并移除默认敏感值。
2. `Makefile` 的 `start/stop-all` 使用了根目录脚本路径（`./start-docker.sh`、`./stop-docker.sh`），而实际脚本位于 `scripts/docker/`；若根目录无同名脚本，命令会失败。
3. API 已开启 CORS `Origin: *`（见 `advanced/api/config/main.php`），生产环境建议按域名白名单收紧。

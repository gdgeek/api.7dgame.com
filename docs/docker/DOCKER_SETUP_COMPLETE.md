# Docker 本地开发环境配置完成 ✅

## 📦 已创建的文件

### 核心配置文件
- ✅ `docker-compose.yml` - Docker Compose 编排配置
- ✅ `.env.docker.example` - 环境变量模板
- ✅ `.dockerignore` - Docker 构建忽略文件
- ✅ `.gitignore` - 已更新，排除敏感文件

### 文档文件
- ✅ `docker/README.zh-CN.md` - 完整的中文使用文档
- ✅ `docker/README.md` - 英文使用文档
- ✅ `DOCKER_QUICK_START.md` - 快速启动指南
- ✅ `README.md` - 已更新主文档

### 脚本文件
- ✅ `start-docker.sh` - 一键启动脚本（可执行）
- ✅ `stop-docker.sh` - 停止服务脚本（可执行）
- ✅ `check-env.sh` - 环境检查脚本（可执行）
- ✅ `Makefile` - 常用命令快捷方式

## 🎯 主要特性

### 1. 完整的服务栈
- **API 服务** (PHP 8.4 + Apache) - 端口 8081
- **后台应用** (PHP 8.4 + Apache) - 端口 8082
- **MySQL 8.0** - 端口 3306
- **Redis Alpine** - 端口 6379
- **phpMyAdmin** - 端口 8080

### 2. 安全配置
- ✅ 所有敏感信息使用环境变量
- ✅ JWT 密钥存储在 Docker volume
- ✅ `.env.docker` 已加入 .gitignore
- ✅ 默认密码仅用于本地开发
- ✅ 生产环境配置指南

### 3. 自动化脚本
- ✅ 一键启动（自动完成所有初始化）
- ✅ 环境检查（检测配置问题）
- ✅ 优雅停止（可选保留或删除数据）
- ✅ Makefile 快捷命令

### 4. 开发友好
- ✅ 代码热重载（volume 挂载）
- ✅ 详细的日志输出
- ✅ 完整的中英文文档
- ✅ 故障排查指南

## 🚀 快速开始

### 方式一：一键启动（推荐）

```bash
# 1. 检查环境
./check-env.sh

# 2. 启动服务
./start-docker.sh

# 3. 访问应用
# API: http://localhost:8081
# Swagger: http://localhost:8081/swagger
# phpMyAdmin: http://localhost:8080
```

### 方式二：使用 Makefile

```bash
make help           # 查看所有命令
make start          # 启动服务
make logs           # 查看日志
make shell          # 进入容器
make stop           # 停止服务
```

### 方式三：手动操作

```bash
# 1. 配置环境
cp .env.docker.example .env.docker
# 编辑 .env.docker

# 2. 生成密钥
mkdir -p jwt_keys
openssl ecparam -genkey -name prime256v1 -noout -out jwt_keys/jwt-key.pem

# 3. 启动
docker-compose up -d

# 4. 初始化
docker-compose exec api php yii migrate --interactive=0
docker-compose exec api php yii rbac/init
```

## 📋 服务访问信息

| 服务 | 地址 | 用户名 | 密码 |
|------|------|--------|------|
| API 服务 | http://localhost:8081 | - | - |
| 后台应用 | http://localhost:8082 | - | - |
| Swagger 文档 | http://localhost:8081/swagger | swagger_admin | YourStrongP@ssw0rd! |
| phpMyAdmin | http://localhost:8080 | root | rootpassword |
| MySQL | localhost:3306 | bujiaban | local_dev_password |
| Redis | localhost:6379 | - | - |

⚠️ **注意**: 这些是默认的开发环境密码，生产环境请务必修改！

## 📚 文档导航

### 快速参考
- 🚀 [快速启动指南](DOCKER_QUICK_START.md) - 最常用的命令和操作
- 📖 [完整中文文档](docker/README.zh-CN.md) - 详细的使用说明
- 📖 [英文文档](docker/README.md) - English documentation

### 项目文档
- 📖 [主 README](README.md) - 项目总览
- 📖 [Swagger 配置](docs/SWAGGER_CONFIG.md) - API 文档配置
- 📖 [部署指南](docs/SWAGGER_DEPLOYMENT.md) - 生产环境部署

## 🔧 常用命令速查

### 服务管理
```bash
make start          # 启动所有服务
make stop           # 停止服务（保留数据）
make restart        # 重启服务
make status         # 查看服务状态
```

### 日志查看
```bash
make logs           # 所有服务日志
make logs-api       # API 日志
make logs-app       # 后台应用日志
make logs-db        # 数据库日志
```

### 开发操作
```bash
make shell          # 进入 API 容器
make migrate        # 运行数据库迁移
make test           # 运行测试
make composer       # 安装依赖
```

### 数据库操作
```bash
make db-backup      # 备份数据库
make db-restore     # 恢复数据库
make db-reset       # 重置数据库
```

## ⚙️ 环境配置

### 必需配置
编辑 `.env.docker` 文件：

```bash
# 数据库配置
MYSQL_PASSWORD=local_dev_password

# JWT 密钥路径
JWT_KEY=/var/www/.ssh/jwt-key.pem
```

### 可选配置（根据需要）

```bash
# 邮件服务（用于密码重置、邮箱验证）
MAILER_USERNAME=your_email@example.com
MAILER_PASSWORD=your_password

# 腾讯云 COS（用于文件上传）
SECRET_ID=your_secret_id
SECRET_KEY=your_secret_key
COS_BUCKETS_PUBLIC_BUCKET=your-bucket

# 微信集成（用于微信登录）
WECHAT_APP_ID=your_app_id
WECHAT_SECRET=your_secret
```

## 🔒 安全注意事项

### 开发环境
- ✅ 使用 `.env.docker` 管理敏感信息
- ✅ JWT 密钥存储在 Docker volume
- ✅ 所有敏感文件已加入 .gitignore

### 生产环境
- ⚠️ 修改所有默认密码
- ⚠️ 使用强密码（16+ 字符）
- ⚠️ 启用 HTTPS
- ⚠️ 限制 Swagger 访问
- ⚠️ 禁用 Debug 模式
- ⚠️ 定期备份数据

## 🐛 故障排查

### 端口冲突
```bash
# 检查端口占用
./check-env.sh

# 修改端口（编辑 docker-compose.yml）
ports:
  - "8181:80"  # 将 8081 改为 8181
```

### 权限问题
```bash
make fix-permissions
```

### 数据库连接失败
```bash
# 查看数据库日志
make logs-db

# 重启数据库
docker-compose restart db
```

### 完全重置
```bash
# 停止并删除所有数据
make stop-all

# 重新启动
make start
```

## 📊 项目结构

```
.
├── docker-compose.yml          # Docker 编排配置
├── .env.docker.example         # 环境变量模板
├── start-docker.sh            # 启动脚本 ⭐
├── stop-docker.sh             # 停止脚本
├── check-env.sh               # 环境检查脚本
├── Makefile                   # 快捷命令
├── DOCKER_QUICK_START.md      # 快速指南
├── docker/
│   ├── README.zh-CN.md        # 中文文档
│   ├── README.md              # 英文文档
│   ├── Local_Api              # API Dockerfile
│   ├── Local_App              # App Dockerfile
│   ├── api-default.conf       # API Apache 配置
│   └── app-default.conf       # App Apache 配置
├── advanced/                  # Yii2 应用
│   ├── api/                  # API 应用
│   ├── backend/              # 后台应用
│   ├── common/               # 共享代码
│   └── console/              # 控制台应用
└── jwt_keys/                 # JWT 密钥（自动生成）
```

## 🎓 学习资源

### Docker 相关
- [Docker 官方文档](https://docs.docker.com/)
- [Docker Compose 文档](https://docs.docker.com/compose/)

### Yii2 相关
- [Yii2 官方文档](https://www.yiiframework.com/doc/guide/2.0/zh-cn)
- [Yii2 API 文档](https://www.yiiframework.com/doc/api/2.0)

### 项目相关
- [Swagger/OpenAPI 规范](https://swagger.io/specification/)
- [JWT 介绍](https://jwt.io/introduction)

## 🤝 获取帮助

如果遇到问题：

1. 📖 查看 [故障排查指南](docker/README.zh-CN.md#故障排查)
2. 🔍 运行环境检查: `./check-env.sh`
3. 📝 查看日志: `make logs`
4. 🐛 [提交 Issue](../../issues)

## ✅ 下一步

现在你可以：

1. ✅ 启动服务: `./start-docker.sh`
2. ✅ 访问 Swagger 文档: http://localhost:8081/swagger
3. ✅ 开始开发你的 API
4. ✅ 运行测试: `make test`
5. ✅ 查看日志: `make logs`

---

**祝开发愉快！** 🎉

如有问题，请查看文档或提交 Issue。

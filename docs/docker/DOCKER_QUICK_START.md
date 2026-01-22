# Docker 快速启动指南 🚀

## 一键启动（最简单）

```bash
./start-docker.sh
```

就这么简单！脚本会自动完成所有配置。

## 服务地址

| 服务 | 地址 | 说明 |
|------|------|------|
| 🌐 API 服务 | http://localhost:8081 | 主 API 接口 |
| 🖥️ 后台应用 | http://localhost:8082 | 后台管理系统 |
| 📖 Swagger 文档 | http://localhost:8081/swagger | API 文档 |
| 🗄️ phpMyAdmin | http://localhost:8080 | 数据库管理 |
| 💾 MySQL | localhost:3306 | 数据库 |
| 🔴 Redis | localhost:6379 | 缓存 |

## 常用命令速查

### 使用 Makefile（推荐）

```bash
make help           # 查看所有命令
make start          # 启动服务
make stop           # 停止服务
make restart        # 重启服务
make logs           # 查看日志
make shell          # 进入容器
make migrate        # 运行迁移
make test           # 运行测试
make db-backup      # 备份数据库
```

### 使用 Docker Compose

```bash
# 服务管理
docker-compose up -d              # 启动
docker-compose down               # 停止
docker-compose restart            # 重启
docker-compose ps                 # 查看状态

# 日志查看
docker-compose logs -f            # 所有日志
docker-compose logs -f api        # API 日志
docker-compose logs -f db         # 数据库日志

# 进入容器
docker-compose exec api bash      # 进入 API 容器
docker-compose exec db bash       # 进入数据库容器

# Yii 命令
docker-compose exec api php yii migrate           # 运行迁移
docker-compose exec api php yii cache/flush-all   # 清除缓存
docker-compose exec api composer install          # 安装依赖
```

## 故障排查

### 端口被占用
编辑 `docker-compose.yml`，修改端口映射：
```yaml
ports:
  - "8181:80"  # 将 8081 改为 8181
```

### 权限问题
```bash
make fix-permissions
# 或
docker-compose exec api chmod -R 777 /var/www/html/advanced/runtime
```

### 数据库连接失败
```bash
# 查看数据库日志
docker-compose logs db

# 重启数据库
docker-compose restart db
```

### 重置所有数据
```bash
make db-reset
# 或
./stop-docker.sh -v
./start-docker.sh
```

## 开发工作流

### 1. 修改代码
代码会自动同步到容器，无需重启。

### 2. 添加新功能
```bash
# 创建迁移
make migrate-create
# 输入迁移名称，如: create_users_table

# 编辑迁移文件
# advanced/console/migrations/m******_create_users_table.php

# 运行迁移
make migrate
```

### 3. 运行测试
```bash
make test              # 所有测试
make test-unit         # 单元测试
make test-integration  # 集成测试
```

### 4. 查看日志
```bash
make logs-api          # API 日志
make logs-app          # 应用日志
make logs-db           # 数据库日志
```

### 5. 数据库操作
```bash
# 备份
make db-backup

# 恢复
make db-restore
# 输入备份文件路径

# 访问 phpMyAdmin
# 浏览器打开: http://localhost:8080
# 用户名: root
# 密码: rootpassword
```

## 环境配置

### 修改数据库密码
编辑 `.env.docker`:
```bash
MYSQL_PASSWORD=your_new_password
```

然后重启：
```bash
docker-compose down -v
docker-compose up -d
```

### 配置邮件服务
编辑 `.env.docker`:
```bash
MAILER_USERNAME=your_email@example.com
MAILER_PASSWORD=your_password
```

重启 API 服务：
```bash
docker-compose restart api
```

### 配置腾讯云 COS
编辑 `.env.docker`:
```bash
SECRET_ID=your_secret_id
SECRET_KEY=your_secret_key
COS_BUCKETS_PUBLIC_BUCKET=your-bucket
```

## 生产部署注意事项

⚠️ **生产环境请务必：**

1. 修改所有默认密码
2. 使用强密码（16+ 字符）
3. 禁用 Debug 模式
4. 启用 HTTPS
5. 限制 Swagger 访问
6. 定期备份数据库
7. 监控日志和性能

## 更多帮助

- 📖 [完整 Docker 文档](docker/README.zh-CN.md)
- 📖 [项目主文档](README.md)
- 📖 [Swagger 配置](docs/SWAGGER_CONFIG.md)
- 🐛 [提交 Issue](../../issues)

---

**提示**: 首次启动可能需要 1-2 分钟下载镜像和初始化数据库，请耐心等待。

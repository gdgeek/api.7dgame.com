# Docker 本地开发环境配置

## 快速开始

### 1. 复制环境配置文件
```bash
cp .env.docker.example .env.docker
```

### 2. 编辑 `.env.docker` 填入你的实际配置
- 更新数据库密码
- 添加邮箱凭据（用于测试邮件功能）
- 添加腾讯云凭据（如果需要测试文件上传）
- 添加微信凭据（如果需要测试微信集成）

### 3. 生成 JWT 密钥（如果不存在）
```bash
mkdir -p jwt_keys
openssl ecparam -genkey -name prime256v1 -noout -out jwt_keys/jwt-key.pem
```

### 4. 构建并启动容器
```bash
docker-compose up -d
```

### 5. 等待数据库完全启动（约 30 秒）
```bash
docker-compose logs -f db
# 看到 "ready for connections" 后按 Ctrl+C 退出
```

### 6. 运行数据库迁移
```bash
docker-compose exec api php yii migrate --interactive=0
```

### 7. 初始化 RBAC 权限系统
```bash
docker-compose exec api php yii rbac/init
```

### 8. 安装 Composer 依赖（如果需要）
```bash
docker-compose exec api composer install
```

## 服务访问地址

| 服务 | 地址 | 说明 |
|------|------|------|
| **API 服务** | http://localhost:8081 | 主 API 接口 |
| **后台应用** | http://localhost:8082 | 后台管理系统 |
| **phpMyAdmin** | http://localhost:8080 | 数据库管理工具 |
| **MySQL** | localhost:3306 | 数据库服务器 |
| **Redis** | localhost:6379 | 缓存服务器 |

### 默认登录凭据

**phpMyAdmin:**
- 服务器: `db`
- 用户名: `root`
- 密码: `rootpassword`

**MySQL 应用用户:**
- 用户名: `bujiaban`
- 密码: `local_dev_password`

## 常用命令

### 查看日志
```bash
# 查看所有服务日志
docker-compose logs -f

# 查看特定服务日志
docker-compose logs -f api
docker-compose logs -f app
docker-compose logs -f db
```

### 进入容器 Shell
```bash
# 进入 API 容器
docker-compose exec api bash

# 进入后台应用容器
docker-compose exec app bash

# 进入数据库容器
docker-compose exec db bash
```

### 运行 Yii 命令
```bash
# 运行迁移
docker-compose exec api php yii migrate

# 创建新迁移
docker-compose exec api php yii migrate/create create_new_table

# 清除缓存
docker-compose exec api php yii cache/flush-all

# 查看所有路由
docker-compose exec api php yii help
```

### 数据库操作
```bash
# 导出数据库
docker-compose exec db mysqldump -u bujiaban -plocal_dev_password bujiaban > backup.sql

# 导入数据库
docker-compose exec -T db mysql -u bujiaban -plocal_dev_password bujiaban < backup.sql

# 进入 MySQL 命令行
docker-compose exec db mysql -u bujiaban -plocal_dev_password bujiaban
```

### 重启服务
```bash
# 重启所有服务
docker-compose restart

# 重启特定服务
docker-compose restart api
docker-compose restart app
```

### 停止服务
```bash
# 停止所有服务（保留数据）
docker-compose down

# 停止并删除所有数据卷（警告：会删除所有数据！）
docker-compose down -v
```

### 重新构建镜像
```bash
# 重新构建所有镜像
docker-compose build

# 重新构建特定服务
docker-compose build api
docker-compose build app

# 重新构建并启动
docker-compose up -d --build
```

## 开发工作流

### 修改代码后
代码修改会自动同步到容器中（通过 volume 挂载），无需重启容器。

### 修改配置文件后
```bash
docker-compose restart api
docker-compose restart app
```

### 添加新的 Composer 包
```bash
docker-compose exec api composer require vendor/package
```

### 运行测试
```bash
# 运行所有测试
docker-compose exec api vendor/bin/codecept run

# 运行单元测试
docker-compose exec api vendor/bin/codecept run unit

# 运行集成测试
docker-compose exec api vendor/bin/codecept run integration
```

## 安全注意事项

⚠️ **重要提醒：**

- **绝对不要** 将 `.env.docker` 提交到版本控制系统
- 生产环境请使用强密码
- 默认密码仅用于本地开发
- JWT 密钥应该为每个环境单独生成
- 不要在代码中硬编码敏感信息

## 故障排查

### 端口冲突
如果端口 8080、8081、8082、3306 或 6379 已被占用，编辑 `docker-compose.yml` 修改端口映射：

```yaml
ports:
  - "8181:80"  # 将 8081 改为 8181
```

### 权限问题
如果遇到文件权限问题：

```bash
# 修复 runtime 目录权限
docker-compose exec api chown -R www-data:www-data /var/www/html/advanced/runtime
docker-compose exec api chmod -R 777 /var/www/html/advanced/runtime

# 修复 web/assets 目录权限
docker-compose exec api chown -R www-data:www-data /var/www/html/advanced/api/web/assets
docker-compose exec api chmod -R 777 /var/www/html/advanced/api/web/assets
```

### 数据库连接失败
确保数据库服务已完全启动：

```bash
docker-compose logs db
# 查找 "ready for connections" 消息
```

如果数据库启动失败，检查数据卷：

```bash
# 删除数据卷并重新创建
docker-compose down -v
docker-compose up -d
```

### Composer 依赖问题
```bash
# 清除 Composer 缓存
docker-compose exec api composer clear-cache

# 重新安装依赖
docker-compose exec api composer install --no-cache
```

### 容器无法启动
```bash
# 查看详细错误信息
docker-compose logs api
docker-compose logs app

# 检查容器状态
docker-compose ps

# 重新构建镜像
docker-compose build --no-cache
docker-compose up -d
```

### Redis 连接问题
```bash
# 测试 Redis 连接
docker-compose exec redis redis-cli ping
# 应该返回 PONG

# 查看 Redis 日志
docker-compose logs redis
```

## 性能优化

### 使用 Redis 缓存
确保在配置文件中启用 Redis 缓存：

```php
'cache' => [
    'class' => 'yii\redis\Cache',
    'redis' => [
        'hostname' => 'redis',
        'port' => 6379,
        'database' => 0,
    ]
],
```

### 数据库查询优化
```bash
# 启用查询日志
docker-compose exec db mysql -u root -prootpassword -e "SET GLOBAL general_log = 'ON';"

# 查看慢查询
docker-compose exec db mysql -u root -prootpassword -e "SHOW VARIABLES LIKE 'slow_query%';"
```

## 备份与恢复

### 备份数据库
```bash
# 创建备份目录
mkdir -p backups

# 备份数据库
docker-compose exec db mysqldump -u bujiaban -plocal_dev_password bujiaban > backups/backup_$(date +%Y%m%d_%H%M%S).sql
```

### 恢复数据库
```bash
# 从备份恢复
docker-compose exec -T db mysql -u bujiaban -plocal_dev_password bujiaban < backups/backup_20260121_120000.sql
```

### 备份上传文件
```bash
# 备份 storage 目录
tar -czf backups/storage_$(date +%Y%m%d_%H%M%S).tar.gz advanced/api/web/storage/
```

## 更多帮助

如果遇到问题，请查看：
- [Yii2 官方文档](https://www.yiiframework.com/doc/guide/2.0/zh-cn)
- [Docker Compose 文档](https://docs.docker.com/compose/)
- 项目 Issues 页面

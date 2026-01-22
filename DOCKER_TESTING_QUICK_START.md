# Docker 本地测试环境快速启动指南

## 🚀 快速开始

### 1. 启动所有服务
```bash
# 使用 Makefile（推荐）
make start

# 或使用 Docker Compose
docker-compose up -d --build
```

### 2. 等待服务就绪
```bash
# 检查容器状态
docker-compose ps

# 查看日志
docker-compose logs -f
```

### 3. 运行数据库迁移
```bash
# 使用 Makefile
make migrate

# 或直接运行
docker-compose exec -T api php yii migrate --interactive=0
```

### 4. 运行单元测试
```bash
# 使用 Makefile
make test-unit

# 或直接运行
docker-compose exec -T api vendor/bin/phpunit

# 查看详细报告
docker-compose exec -T api vendor/bin/phpunit --testdox
```

## 📊 测试结果

✅ **所有测试通过！**
- 总测试: 97
- 断言: 4,266
- 通过: 93
- 跳过: 4
- 执行时间: ~2分20秒

## 🔧 服务访问

| 服务 | 地址 | 说明 |
|------|------|------|
| API 服务 | http://localhost:8081 | Yii2 API 应用 |
| 后台应用 | http://localhost:8082 | Yii2 后台管理 |
| phpMyAdmin | http://localhost:8080 | 数据库管理 |
| MySQL | localhost:3306 | 数据库服务 |
| Redis | localhost:6379 | 缓存服务 |

### 数据库连接信息
- **主机**: localhost (或容器内使用 `db`)
- **端口**: 3306
- **数据库**: bujiaban
- **用户名**: bujiaban
- **密码**: local_dev_password

## 🛠️ 常用命令

### Makefile 命令
```bash
make help              # 查看所有可用命令
make start             # 启动所有服务
make stop              # 停止服务（保留数据）
make restart           # 重启服务
make logs              # 查看所有日志
make logs-api          # 查看 API 日志
make logs-db           # 查看数据库日志
make test              # 运行所有测试
make test-unit         # 运行单元测试
make shell             # 进入 API 容器
make db-backup         # 备份数据库
make cache-flush       # 清除缓存
make fix-permissions   # 修复权限
```

### Docker Compose 命令
```bash
# 容器管理
docker-compose up -d              # 启动服务
docker-compose down               # 停止服务
docker-compose down -v            # 停止并删除数据卷
docker-compose restart            # 重启服务
docker-compose ps                 # 查看容器状态

# 日志查看
docker-compose logs -f            # 查看所有日志
docker-compose logs -f api        # 查看 API 日志
docker-compose logs -f db         # 查看数据库日志

# 进入容器
docker-compose exec api bash      # 进入 API 容器
docker-compose exec db bash       # 进入数据库容器

# 执行命令
docker-compose exec api php yii migrate        # 运行迁移
docker-compose exec api composer install       # 安装依赖
docker-compose exec api vendor/bin/phpunit     # 运行测试
```

## 🧪 测试相关命令

### 运行测试
```bash
# 运行所有单元测试
docker-compose exec -T api vendor/bin/phpunit

# 运行特定测试类
docker-compose exec -T api vendor/bin/phpunit tests/unit/models/UserTest.php

# 运行特定测试方法
docker-compose exec -T api vendor/bin/phpunit --filter testCreateUser

# 查看详细测试报告
docker-compose exec -T api vendor/bin/phpunit --testdox

# 生成代码覆盖率报告（需要 Xdebug）
docker-compose exec -T api vendor/bin/phpunit --coverage-html coverage/
```

### 测试调试
```bash
# 显示详细输出
docker-compose exec -T api vendor/bin/phpunit --verbose

# 显示调试信息
docker-compose exec -T api vendor/bin/phpunit --debug

# 停止在第一个错误
docker-compose exec -T api vendor/bin/phpunit --stop-on-error

# 停止在第一个失败
docker-compose exec -T api vendor/bin/phpunit --stop-on-failure
```

## 🔍 故障排查

### 容器无法启动
```bash
# 查看容器日志
docker-compose logs api

# 检查端口占用
lsof -i :8081
lsof -i :3306
lsof -i :6379

# 清理并重新启动
docker-compose down -v
docker-compose up -d --build
```

### 数据库连接失败
```bash
# 检查数据库容器状态
docker-compose ps db

# 测试数据库连接
docker-compose exec db mysql -u bujiaban -plocal_dev_password -e "SELECT 1"

# 查看数据库日志
docker-compose logs db
```

### 测试失败
```bash
# 清理测试数据库
docker-compose exec api php yii migrate/fresh --interactive=0

# 重新运行迁移
docker-compose exec api php yii migrate --interactive=0

# 清除缓存
docker-compose exec api php yii cache/flush-all

# 检查 Redis 连接
docker-compose exec redis redis-cli ping
```

### 权限问题
```bash
# 修复文件权限
make fix-permissions

# 或手动修复
docker-compose exec api chown -R www-data:www-data /var/www/html/advanced/runtime
docker-compose exec api chmod -R 777 /var/www/html/advanced/runtime
```

## 📝 测试配置文件

### 关键配置文件
- `advanced/phpunit.xml` - PHPUnit 配置
- `advanced/test_bootstrap.php` - 测试引导文件
- `advanced/common/config/test-local.php` - 测试环境配置
- `advanced/tests/unit.suite.yml` - Codeception 单元测试套件

### 测试环境变量
测试环境使用以下配置：
- **数据库主机**: `db` (Docker 容器名)
- **Redis 主机**: `redis` (Docker 容器名)
- **数据库名**: `bujiaban`
- **Redis 数据库**: `1` (测试专用)

## 🎯 测试覆盖范围

### 已测试模块
- ✅ 邮箱验证表单 (24 个测试)
- ✅ 邮箱验证服务 (7 个测试)
- ✅ 密码重置服务 (8 个测试)
- ✅ 频率限制器 (17 个测试)
- ✅ Redis 键管理 (9 个测试)
- ✅ 用户模型 (21 个测试)

### 测试统计
- **总测试数**: 97
- **总断言数**: 4,266
- **通过率**: 95.9% (93/97)
- **跳过**: 4 个测试

## 🔄 持续集成

### GitHub Actions
测试已集成到 CI/CD 流程：
```yaml
# .github/workflows/ci.yml
- name: Run Unit Tests
  run: |
    docker-compose exec -T api vendor/bin/phpunit
```

### 本地 CI 模拟
```bash
# 模拟 CI 环境运行测试
docker-compose down -v
docker-compose up -d --build
sleep 30
docker-compose exec -T api php yii migrate --interactive=0
docker-compose exec -T api vendor/bin/phpunit
```

## 📚 相关文档

- [完整测试报告](./DOCKER_TEST_REPORT.md)
- [Docker 设置指南](./docs/docker/DOCKER_QUICK_START.md)
- [CI 监控指南](./docs/docker/CI_MONITORING_GUIDE.md)
- [邮件功能指南](./docs/email/QUICK_REFERENCE.md)

## 💡 提示

1. **首次启动**: 首次启动可能需要 2-3 分钟来构建镜像和初始化数据库
2. **数据持久化**: 使用 `docker-compose down` 而不是 `down -v` 来保留数据
3. **性能优化**: 在 macOS 上，考虑使用 Docker Desktop 的 VirtioFS 提升性能
4. **测试隔离**: 每个测试使用独立的 Redis 数据库 (database=1)
5. **日志查看**: 使用 `docker-compose logs -f` 实时查看所有服务日志

## ✅ 验证清单

启动后验证以下项目：

- [ ] 所有容器都在运行 (`docker-compose ps`)
- [ ] 数据库可以连接 (`docker-compose exec db mysql -u bujiaban -plocal_dev_password -e "SELECT 1"`)
- [ ] Redis 可以连接 (`docker-compose exec redis redis-cli ping`)
- [ ] API 服务可访问 (`curl http://localhost:8081`)
- [ ] 数据库迁移已完成 (`docker-compose exec api php yii migrate/history`)
- [ ] 所有测试通过 (`docker-compose exec -T api vendor/bin/phpunit`)

---

**最后更新**: 2026-01-22  
**PHP 版本**: 8.4.17  
**PHPUnit 版本**: 12.5.4  
**MySQL 版本**: 8.0  
**Redis 版本**: Alpine

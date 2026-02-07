# 测试环境配置说明

## 概述

本项目的测试环境支持两种运行模式：
1. **本地 Docker 环境** - 使用 Docker Compose 运行
2. **CI 环境** - GitHub Actions 运行

## 环境自动检测

`advanced/test_bootstrap.php` 会自动检测运行环境并使用相应的配置：

```php
// 检测是否在 Docker 环境中
$isDocker = getenv('DOCKER_ENV') === 'true' || file_exists('/.dockerenv');
```

### 检测逻辑

1. **Docker 环境检测**：
   - 检查环境变量 `DOCKER_ENV=true`
   - 或检查 `/.dockerenv` 文件是否存在（Docker 容器标识）

2. **配置切换**：
   - Docker 环境：使用容器名称（`db`, `redis`）
   - CI 环境：使用 localhost（`127.0.0.1`, `localhost`）

## 配置对比

### 数据库配置

| 环境 | 主机 | 数据库名 | 用户名 | 密码 |
|------|------|----------|--------|------|
| Docker 本地 | `db` | `bujiaban` | `bujiaban` | `local_dev_password` |
| GitHub Actions CI | `127.0.0.1` | `yii2_advanced_test` | `root` | `root` |

### Redis 配置

| 环境 | 主机 | 端口 | 数据库 |
|------|------|------|--------|
| Docker 本地 | `redis` | 6379 | 1 |
| GitHub Actions CI | `127.0.0.1` | 6379 | 1 |

## 本地 Docker 环境

### 启动测试
```bash
# 启动容器
docker-compose up -d

# 运行测试（自动检测为 Docker 环境）
docker-compose exec -T api vendor/bin/phpunit
```

### 环境特征
- ✅ 自动检测 `/.dockerenv` 文件
- ✅ 使用 Docker 服务名称（`db`, `redis`）
- ✅ 使用本地开发数据库凭据

## GitHub Actions CI 环境

### 配置文件
`.github/workflows/ci.yml`

### 服务配置
```yaml
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: yii2_advanced_test
    ports:
      - 3306:3306
      
  redis:
    image: redis
    ports:
      - 6379:6379
```

### 环境特征
- ✅ 使用 GitHub Actions 服务容器
- ✅ 通过 localhost 访问服务
- ✅ 使用 CI 专用数据库凭据

## 手动指定环境

如果需要手动指定环境，可以设置环境变量：

### 强制使用 Docker 配置
```bash
export DOCKER_ENV=true
vendor/bin/phpunit
```

### 强制使用 CI 配置
```bash
unset DOCKER_ENV
vendor/bin/phpunit
```

## 配置文件层次

测试配置通过多个文件合并：

```
test_bootstrap.php (全局引导)
    ↓
common/config/codeception-local.php (Codeception 配置)
    ↓
common/config/test-local.php (测试环境配置)
```

### 优先级
1. `test_bootstrap.php` - 最高优先级，环境检测
2. `codeception-local.php` - Codeception 特定配置
3. `test-local.php` - 测试环境本地配置（被 .gitignore）

## 故障排查

### 问题：测试连接数据库失败

**Docker 环境**：
```bash
# 检查容器是否运行
docker-compose ps

# 测试数据库连接
docker-compose exec db mysql -u bujiaban -plocal_dev_password -e "SELECT 1"

# 检查环境检测
docker-compose exec api php -r "echo file_exists('/.dockerenv') ? 'Docker' : 'Not Docker';"
```

**CI 环境**：
```bash
# 检查服务状态
mysql -h 127.0.0.1 -u root -proot -e "SELECT 1"

# 检查 Redis
redis-cli -h 127.0.0.1 ping
```

### 问题：环境检测错误

如果自动检测失败，可以手动设置：

```bash
# Docker 环境
export DOCKER_ENV=true

# CI 环境
export DOCKER_ENV=false
```

## 最佳实践

### 1. 本地开发
- 使用 Docker Compose 保持环境一致
- 不要修改 `test_bootstrap.php` 的环境检测逻辑
- 使用 `test-local.php` 进行本地配置覆盖

### 2. CI 配置
- 确保 `.github/workflows/ci.yml` 中的服务配置正确
- 使用标准的 MySQL 和 Redis 镜像
- 保持数据库凭据与 `test_bootstrap.php` 一致

### 3. 配置管理
- `test-local.php` 应该在 `.gitignore` 中
- 不要在代码中硬编码敏感信息
- 使用环境变量或配置文件管理凭据

## 测试验证

### 验证 Docker 环境
```bash
# 启动容器
docker-compose up -d

# 运行测试
docker-compose exec -T api vendor/bin/phpunit

# 预期结果
# Tests: 97, Assertions: 4266, Skipped: 4
# OK, but some tests were skipped!
```

### 验证 CI 环境
```bash
# 推送到 GitHub
git push origin master

# 查看 GitHub Actions
# https://github.com/your-repo/actions

# 预期结果
# ✅ test job 通过
# ✅ build job 通过（仅主分支）
# ✅ deploy job 通过（仅主分支）
```

## 环境变量参考

| 变量名 | 用途 | Docker 值 | CI 值 |
|--------|------|-----------|-------|
| `DOCKER_ENV` | 强制指定环境 | `true` | 未设置 |
| `YII_DEBUG` | 调试模式 | `true` | `true` |
| `YII_ENV` | 运行环境 | `test` | `test` |

## 相关文档

- [Docker 测试快速启动](./DOCKER_TESTING_QUICK_START.md)
- [Docker 测试报告](./DOCKER_TEST_REPORT.md)
- [CI 监控指南](./docs/docker/CI_MONITORING_GUIDE.md)

## 更新日志

### 2026-01-22
- ✅ 添加环境自动检测功能
- ✅ 支持 Docker 和 CI 环境自动切换
- ✅ 统一测试配置管理

---

**维护者**: 开发团队  
**最后更新**: 2026-01-22

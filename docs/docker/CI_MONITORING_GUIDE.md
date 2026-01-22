# CI 构建监控指南

## 快速链接

**GitHub Actions 页面：**
https://github.com/gdgeek/api.7dgame.com/actions

## 当前构建状态

最新推送：
- 分支：`master`
- 提交：`6dbe020d` - "docs: 添加 CI 构建触发总结文档"
- 时间：2026-01-22

## 监控步骤

### 1. 查看构建状态

访问 GitHub Actions 页面，查看最新的 workflow 运行状态：
- 🟡 黄色圆圈：构建进行中
- ✅ 绿色勾号：构建成功
- ❌ 红色叉号：构建失败

### 2. 查看构建日志

点击具体的 workflow 运行，可以查看详细日志：
- Build and Push Docker Image
- 查看 Composer 安装步骤
- 确认 vendor 目录创建成功

### 3. 验证修复

构建成功后，关键验证点：

#### a. 检查 Docker 镜像
```bash
# 拉取最新镜像
docker pull registry.cn-beijing.aliyuncs.com/gdgeek/api.7dgame.com:latest

# 运行容器
docker run -it --rm registry.cn-beijing.aliyuncs.com/gdgeek/api.7dgame.com:latest bash

# 在容器内检查
ls -la /var/www/html/advanced/vendor/
ls -la /var/www/html/advanced/vendor/autoload.php
```

#### b. 测试 API 端点
```bash
# 健康检查
curl http://your-api-domain/v1/health

# 版本信息
curl http://your-api-domain/v1/version
```

## 预期构建时间

- 正常构建时间：5-10 分钟
- 包含 Composer 安装：可能需要 10-15 分钟（首次）

## 常见问题

### Q1: 构建失败 - Composer 安装错误
**解决方案：**
- 检查 `composer.json` 和 `composer.lock` 是否正确
- 确认 PHP 版本兼容性
- 查看具体错误日志

### Q2: 构建成功但 vendor 目录仍然缺失
**解决方案：**
- 检查 Dockerfile 中的 COPY 命令顺序
- 确认 `.dockerignore` 没有排除 vendor 目录
- 验证 Composer 安装步骤在 COPY 之后执行

### Q3: 推送到 Registry 失败
**解决方案：**
- 检查 Registry 凭证配置
- 确认网络连接正常
- 查看 Registry 配额和权限

## 关键修复内容

在 `docker/Release` 中添加的内容：

```dockerfile
# 安装 Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 安装 unzip（Composer 需要）
RUN apt-get update && apt-get install -y unzip && \
    rm -rf /var/lib/apt/lists/*

# 复制代码后安装依赖
COPY ./advanced /var/www/html/advanced
RUN cd /var/www/html/advanced && \
    composer install --no-dev --optimize-autoloader --no-interaction
```

## 成功标志

构建成功的标志：
1. ✅ GitHub Actions 显示绿色勾号
2. ✅ Docker 镜像成功推送到 Registry
3. ✅ 容器内存在 `/var/www/html/advanced/vendor/autoload.php`
4. ✅ API 端点正常响应

## 下一步

构建成功后：
1. 更新部署环境使用新镜像
2. 验证所有 API 端点正常工作
3. 测试邮箱验证功能
4. 更新文档标记此问题已解决

## 相关文档

- [Docker Composer 修复文档](./DOCKER_COMPOSER_FIX.md)
- [CI 触发总结](./CI_TRIGGER_SUMMARY.md)
- [CI 配置文件](../../.github/workflows/ci.yml)

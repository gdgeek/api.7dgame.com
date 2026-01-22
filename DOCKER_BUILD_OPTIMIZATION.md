# Docker 构建优化说明

## 优化内容

### 问题
原始的 Dockerfile 在每次构建时都会重新安装所有 Composer 依赖，即使依赖没有变化。

### 解决方案
利用 Docker 层缓存机制，将 Composer 依赖安装分离为独立的层。

## 优化前后对比

### 优化前 ❌
```dockerfile
COPY ./advanced/ /var/www/html/advanced
WORKDIR /var/www/html/advanced
RUN composer install --no-dev --optimize-autoloader --no-interaction
```

**问题**:
- 每次代码变更都会重新安装依赖
- 构建时间长（每次 5-10 分钟）
- 浪费 CI/CD 时间和资源

### 优化后 ✅
```dockerfile
WORKDIR /var/www/html/advanced

# 1. 先复制 composer 文件
COPY ./advanced/composer.json ./advanced/composer.lock ./

# 2. 安装依赖（这一层会被缓存）
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# 3. 复制应用代码
COPY ./advanced/ /var/www/html/advanced/

# 4. 运行后续脚本
RUN composer run-script post-install-cmd --no-interaction || true
```

**优势**:
- ✅ 依赖未变化时使用缓存（构建时间 < 1 分钟）
- ✅ 只有依赖变化时才重新安装（构建时间 5-10 分钟）
- ✅ 节省 CI/CD 时间和成本
- ✅ 加快开发迭代速度

## 工作原理

### Docker 层缓存机制

Docker 按顺序执行 Dockerfile 中的指令，每个指令创建一个新层：

```
Layer 1: FROM php:8.4-apache
Layer 2: RUN apt-get install ...
Layer 3: COPY composer.json composer.lock
Layer 4: RUN composer install        ← 这层会被缓存
Layer 5: COPY ./advanced/
Layer 6: RUN composer run-script
```

### 缓存触发条件

| 场景 | composer.json 变化 | 代码变化 | Layer 4 缓存 | 构建时间 |
|------|-------------------|---------|-------------|---------|
| 首次构建 | - | - | ❌ 无缓存 | ~10 分钟 |
| 只改代码 | ❌ 未变化 | ✅ 变化 | ✅ 使用缓存 | ~1 分钟 |
| 改依赖 | ✅ 变化 | ✅ 变化 | ❌ 重新构建 | ~10 分钟 |

## 构建时间对比

### 场景 1: 首次构建
```
优化前: ~10 分钟
优化后: ~10 分钟
节省: 0 分钟
```

### 场景 2: 代码变更（依赖未变）
```
优化前: ~10 分钟 (重新安装依赖)
优化后: ~1 分钟 (使用缓存)
节省: ~9 分钟 (90% 时间)
```

### 场景 3: 依赖变更
```
优化前: ~10 分钟
优化后: ~10 分钟
节省: 0 分钟
```

## 实际效果

### 典型开发流程

假设一天有 10 次代码提交：
- 9 次只改代码（依赖未变）
- 1 次改依赖

#### 优化前
```
9 次 × 10 分钟 = 90 分钟
1 次 × 10 分钟 = 10 分钟
总计: 100 分钟
```

#### 优化后
```
9 次 × 1 分钟 = 9 分钟
1 次 × 10 分钟 = 10 分钟
总计: 19 分钟
```

**节省**: 81 分钟/天 (81% 时间)

## 最佳实践

### 1. 分层原则
按变化频率从低到高排列：
```dockerfile
# 很少变化
FROM php:8.4-apache
RUN apt-get install ...

# 偶尔变化
COPY composer.json composer.lock ./
RUN composer install

# 经常变化
COPY ./advanced/ /var/www/html/advanced/
```

### 2. 使用 .dockerignore
排除不需要的文件，减少构建上下文：
```
# .dockerignore
.git
.github
node_modules
vendor
*.md
tests
```

### 3. 多阶段构建
对于更复杂的应用：
```dockerfile
# 构建阶段
FROM php:8.4-apache as builder
COPY composer.json composer.lock ./
RUN composer install

# 运行阶段
FROM php:8.4-apache
COPY --from=builder /app/vendor ./vendor
COPY . .
```

## 验证优化效果

### 测试缓存
```bash
# 首次构建
docker build -t test:v1 -f docker/Release .
# 记录时间: ~10 分钟

# 修改代码（不改 composer.json）
echo "// test" >> advanced/api/web/index.php

# 再次构建
docker build -t test:v2 -f docker/Release .
# 记录时间: ~1 分钟 ✅

# 查看缓存使用情况
docker build -t test:v3 -f docker/Release . 2>&1 | grep "CACHED"
```

### 查看层信息
```bash
# 查看镜像层
docker history test:v2

# 查看层大小
docker image inspect test:v2 --format='{{.Size}}'
```

## CI/CD 配置

### GitHub Actions
```yaml
- name: Build and Push Docker Image
  run: |
    # 使用 BuildKit 获得更好的缓存
    export DOCKER_BUILDKIT=1
    
    # 构建镜像
    docker build \
      --cache-from hkccr.ccs.tencentyun.com/gdgeek/api:latest \
      -t hkccr.ccs.tencentyun.com/gdgeek/api:${SHORT_SHA} \
      -f docker/Release .
```

### 使用远程缓存
```yaml
- name: Build with Cache
  uses: docker/build-push-action@v5
  with:
    context: .
    file: docker/Release
    push: true
    tags: hkccr.ccs.tencentyun.com/gdgeek/api:latest
    cache-from: type=registry,ref=hkccr.ccs.tencentyun.com/gdgeek/api:latest
    cache-to: type=inline
```

## 监控和度量

### 构建时间追踪
```bash
# 记录构建时间
time docker build -t test -f docker/Release .

# 或使用 BuildKit
DOCKER_BUILDKIT=1 docker build \
  --progress=plain \
  -t test \
  -f docker/Release . 2>&1 | tee build.log

# 分析构建日志
grep "CACHED" build.log | wc -l  # 缓存层数
```

### CI 指标
在 GitHub Actions 中追踪：
- 平均构建时间
- 缓存命中率
- 镜像大小变化

## 故障排查

### 缓存未生效

**问题**: 每次都重新安装依赖

**检查**:
```bash
# 1. 确认 composer.json 未变化
git diff HEAD~1 advanced/composer.json

# 2. 清理构建缓存
docker builder prune

# 3. 使用 --no-cache 强制重建
docker build --no-cache -t test -f docker/Release .
```

### 缓存过期

**问题**: 使用了旧的依赖

**解决**:
```bash
# 清理特定镜像的缓存
docker rmi $(docker images -f "dangling=true" -q)

# 或强制重新构建
docker build --pull --no-cache -t test -f docker/Release .
```

## 相关资源

- [Docker 最佳实践](https://docs.docker.com/develop/dev-best-practices/)
- [Dockerfile 参考](https://docs.docker.com/engine/reference/builder/)
- [BuildKit 文档](https://docs.docker.com/build/buildkit/)

## 更新日志

### 2026-01-22
- ✅ 添加 Composer 依赖缓存层
- ✅ 优化构建顺序
- ✅ 添加 --no-scripts 标志
- ✅ 分离 post-install 脚本

---

**维护者**: 开发团队  
**最后更新**: 2026-01-22  
**预期效果**: 节省 80%+ 构建时间

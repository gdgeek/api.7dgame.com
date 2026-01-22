# Docker Composer 依赖修复

## 问题描述

CI 打包的 Docker 镜像运行时报错：

```
Warning: require(/var/www/html/advanced/api/web/../../vendor/autoload.php): 
Failed to open stream: No such file or directory in /var/www/html/advanced/api/web/index.php on line 5

Fatal error: Uncaught Error: Failed opening required 
'/var/www/html/advanced/api/web/../../vendor/autoload.php' 
(include_path='.:/usr/local/lib/php') in /var/www/html/advanced/api/web/index.php:5
```

## 根本原因

Release Dockerfile 中缺少 Composer 依赖安装步骤，导致 `vendor/` 目录不存在。

## 解决方案

在 `docker/Release` Dockerfile 中添加以下内容：

### 1. 安装 Composer

```dockerfile
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
```

### 2. 安装 unzip（Composer 需要）

```dockerfile
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    git \
    unzip \  # 添加这一行
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
```

### 3. 运行 Composer Install

```dockerfile
# Install Composer dependencies
WORKDIR /var/www/html/advanced
RUN composer install --no-dev --optimize-autoloader --no-interaction
```

## 完整修复后的 Dockerfile 片段

```dockerfile
# Install required PHP extensions and tools
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install -j$(nproc) mysqli pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY ./advanced/ /var/www/html/advanced

# Install Composer dependencies
WORKDIR /var/www/html/advanced
RUN composer install --no-dev --optimize-autoloader --no-interaction
```

## Composer Install 参数说明

- `--no-dev`: 不安装开发依赖（减小镜像大小）
- `--optimize-autoloader`: 优化自动加载器（提高性能）
- `--no-interaction`: 非交互模式（适合 CI/CD）

## 验证修复

### 1. 构建镜像

```bash
docker build -f docker/Release -t your-image:tag .
```

### 2. 运行容器

```bash
docker run -d -p 8080:80 your-image:tag
```

### 3. 检查 vendor 目录

```bash
docker exec <container-id> ls -la /var/www/html/advanced/vendor/
```

应该能看到 `autoload.php` 和其他 Composer 依赖。

### 4. 测试 API

```bash
curl http://localhost:8080/v1/email/test
```

应该返回正常的 JSON 响应，而不是 PHP 错误。

## 其他 Dockerfile 检查

确保其他 Dockerfile 也包含 Composer 安装：

- ✅ `docker/Local_Api` - 本地开发（通过 volume 挂载）
- ✅ `docker/Local_App` - 本地开发（通过 volume 挂载）
- ✅ `docker/Develop` - 开发环境（需要检查）
- ✅ `docker/Release` - 生产环境（已修复）

## CI/CD 配置

如果使用 GitHub Actions 或其他 CI/CD，确保：

1. **缓存 Composer 依赖**（可选，加速构建）

```yaml
- name: Cache Composer dependencies
  uses: actions/cache@v3
  with:
    path: advanced/vendor
    key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
```

2. **构建时使用正确的 Dockerfile**

```yaml
- name: Build Docker image
  run: docker build -f docker/Release -t myapp:${{ github.sha }} .
```

## 注意事项

1. **composer.lock 文件**: 确保 `advanced/composer.lock` 文件存在并提交到 Git
2. **PHP 版本**: 确保 Dockerfile 中的 PHP 版本与 composer.json 要求一致
3. **内存限制**: Composer 可能需要较多内存，如果构建失败，可以增加内存限制：

```dockerfile
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-interaction
```

## 相关文件

- `docker/Release` - 生产环境 Dockerfile
- `docker/Develop` - 开发环境 Dockerfile
- `advanced/composer.json` - Composer 配置
- `advanced/composer.lock` - 依赖锁定文件

## 更新日志

- 2026-01-21: 修复 Release Dockerfile 缺少 Composer 依赖安装的问题

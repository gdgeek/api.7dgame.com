# 健康检查和版本号 API 文档

## 项目信息

- **项目名称**: AR创作平台
- **项目描述**: AR创作平台后端 API 系统

## 1. 健康检查接口

### 接口信息
- **URL**: `/site/health`
- **方法**: `GET`
- **认证**: 不需要

### 响应示例

```json
{
  "status": "ok",
  "timestamp": 1705593600,
  "datetime": "2024-01-18 12:00:00",
  "database": "connected",
  "cache": "ok"
}
```

### 响应字段说明

| 字段 | 类型 | 说明 |
|------|------|------|
| status | string | 整体状态: `ok` 或 `error` |
| timestamp | integer | Unix 时间戳 |
| datetime | string | 可读时间格式 |
| database | string | 数据库状态: `connected` 或 `disconnected` |
| cache | string | 缓存状态: `ok`, `error` 或 `not_configured` |

### 使用场景
- Kubernetes/Docker 健康检查
- 负载均衡器健康探测
- 监控系统状态检查

---

## 2. 版本号查询接口

### 接口信息
- **URL**: `/site/version`
- **方法**: `GET`
- **认证**: 不需要

### 响应示例

```json
{
  "app_name": "yiisoft/yii2-app-advanced",
  "version": "1.0.0",
  "yii_version": "2.0.53",
  "php_version": "8.4.7",
  "environment": "dev",
  "debug": true
}
```

### 响应字段说明

| 字段 | 类型 | 说明 |
|------|------|------|
| app_name | string | 应用名称 (来自 composer.json) |
| version | string | 应用版本号 (来自 composer.json) |
| yii_version | string | Yii 框架版本 |
| php_version | string | PHP 版本 |
| environment | string | 运行环境: `dev`, `prod`, `test` |
| debug | boolean | 是否开启调试模式 |

### 使用场景
- 版本管理和追踪
- 部署验证
- 问题排查时确认版本信息

---

## 测试方法

### 1. 使用 curl 测试

```bash
# 健康检查
curl http://your-domain/site/health

# 版本信息
curl http://your-domain/site/version
```

### 2. 使用 PHP 脚本测试

```bash
cd advanced
php tests/manual/test_health_version.php
```

### 3. 浏览器访问

直接在浏览器中访问:
- `http://your-domain/site/health`
- `http://your-domain/site/version`

---

## Docker/Kubernetes 配置示例

### Docker Compose

```yaml
services:
  api:
    image: your-api-image
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/site/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
```

### Kubernetes

```yaml
apiVersion: v1
kind: Pod
metadata:
  name: api-pod
spec:
  containers:
  - name: api
    image: your-api-image
    livenessProbe:
      httpGet:
        path: /site/health
        port: 80
      initialDelaySeconds: 30
      periodSeconds: 10
    readinessProbe:
      httpGet:
        path: /site/health
        port: 80
      initialDelaySeconds: 5
      periodSeconds: 5
```

---

## 版本号管理

在 `composer.json` 中更新版本号:

```json
{
  "name": "yiisoft/yii2-app-advanced",
  "version": "1.0.0",
  ...
}
```

建议使用语义化版本 (Semantic Versioning):
- **主版本号**: 不兼容的 API 修改
- **次版本号**: 向下兼容的功能性新增
- **修订号**: 向下兼容的问题修正

例如: `1.2.3` → `主.次.修订`

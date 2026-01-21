# Yii2 Advanced API Backend

<p align="center">
  <img src="https://avatars0.githubusercontent.com/u/993323" height="100px" alt="Yii Framework">
</p>

<p align="center">
  <strong>基于 Yii2 Advanced 的企业级 RESTful API 后端系统</strong>
</p>

<p align="center">
  <a href="#主要特性">特性</a> •
  <a href="#技术栈">技术栈</a> •
  <a href="#快速开始">快速开始</a> •
  <a href="#api-文档">API 文档</a> •
  <a href="#项目结构">项目结构</a> •
  <a href="#开发指南">开发指南</a> •
  <a href="#docker-部署">Docker</a>
</p>

---

## 📋 项目简介

这是一个基于 Yii2 Advanced 模板构建的企业级 RESTful API 后端系统，提供完整的用户认证、资源管理、教育管理等功能模块。项目采用模块化设计，支持多版本 API，集成了完整的 OpenAPI 3.0 文档系统，并提供开箱即用的 Docker 容器化部署方案。

## ✨ 主要特性

### 核心功能
- 🔐 **JWT 认证系统** - 基于 JWT Token 的用户认证和授权
- 👥 **用户管理** - 完整的用户注册、登录、信息管理
- 🍎 **Apple ID 集成** - 支持 Apple ID 第三方登录
- 💬 **微信集成** - 微信登录和支付功能
- 📁 **文件管理** - 文件上传、存储和管理
- 🏷️ **标签系统** - 灵活的标签分类管理
- 📧 **邮件系统** - 完整的邮件发送功能（验证码、密码重置、邮箱验证）

### 业务模块
- 📚 **教育管理** - 学校、班级、教师、学生管理
- 👥 **群组系统** - 群组创建、成员管理、内容共享
- 🎨 **资源管理** - Meta、Prefab、Resource 等资源管理
- 📸 **照片类型** - 照片分类和管理
- 🔧 **工具接口** - 各类辅助工具接口

### 技术特性
- 📖 **OpenAPI 3.0** - 完整的 Swagger API 文档
- 🔒 **HTTP Basic Auth** - Swagger 文档访问保护
- 🐳 **Docker 支持** - 完整的 Docker 容器化部署
- 🌐 **CORS 支持** - 跨域资源共享配置
- 🔄 **RESTful 设计** - 标准的 REST API 设计
- 📊 **RBAC 权限** - 基于角色的访问控制

## 🛠 技术栈

### 后端框架
- **PHP** 8.4+
- **Yii2** 2.0.51 - 高性能 PHP 框架
- **MySQL** - 关系型数据库
- **Redis** - 缓存和会话存储

### 核心依赖
```json
{
  "yiisoft/yii2": "~2.0.51",
  "yiisoft/yii2-symfonymailer": "^4.0",
  "bizley/jwt": "^4.0",
  "lcobucci/jwt": "^5.0",
  "zircote/swagger-php": "^4.0",
  "doctrine/annotations": "^2.0",
  "w7corp/easywechat": "6.0.0",
  "firebase/php-jwt": "^6.0",
  "patrickbussmann/oauth2-apple": "^0.2",
  "tencentcloud/sts": "^3.0"
}
```

### 开发工具
- **Codeception** - 测试框架
- **Yii2 Debug** - 调试工具
- **Yii2 Gii** - 代码生成器
- **PHPUnit** - 单元测试

## 🚀 快速开始

### 环境要求

- PHP >= 8.4
- MySQL >= 8.0
- Redis (推荐)
- Composer
- Docker & Docker Compose (推荐)

### 方式一：使用 Docker 一键启动（推荐）⭐

这是最简单快速的方式，适合本地开发和测试。

1. **克隆项目**
```bash
git clone <repository-url>
cd api.7dgame.com
```

2. **一键启动**
```bash
./start-docker.sh
```

脚本会自动完成：
- ✅ 创建环境配置文件
- ✅ 生成 JWT 密钥
- ✅ 构建 Docker 镜像
- ✅ 启动所有服务（API、数据库、Redis、phpMyAdmin）
- ✅ 运行数据库迁移
- ✅ 初始化 RBAC 权限系统
- ✅ 设置文件权限

3. **访问应用**
- **API 服务**: http://localhost:8081
- **后台应用**: http://localhost:8082
- **Swagger 文档**: http://localhost:8081/swagger
- **phpMyAdmin**: http://localhost:8080

4. **常用命令**
```bash
# 使用 Makefile 简化操作
make help           # 查看所有可用命令
make logs           # 查看日志
make shell          # 进入容器
make migrate        # 运行迁移
make test           # 运行测试
make stop           # 停止服务

# 或使用 docker-compose
docker-compose logs -f api      # 查看 API 日志
docker-compose exec api bash    # 进入 API 容器
docker-compose restart          # 重启服务
```

📖 **详细文档**: [Docker 使用指南](docker/README.zh-CN.md)

### 方式二：使用 Docker Compose 手动部署

如果你想更精细地控制部署过程：

1. **配置环境变量**
```bash
cp .env.docker.example .env.docker
# 编辑 .env.docker 文件，填入你的配置
```

2. **生成 JWT 密钥**
```bash
mkdir -p jwt_keys
openssl ecparam -genkey -name prime256v1 -noout -out jwt_keys/jwt-key.pem
```

3. **启动服务**
```bash
docker-compose up -d
```

4. **等待数据库启动（约 30 秒）**
```bash
docker-compose logs -f db
# 看到 "ready for connections" 后按 Ctrl+C
```

5. **运行迁移和初始化**
```bash
docker-compose exec api php yii migrate --interactive=0
docker-compose exec api php yii rbac/init
```

### 方式三：手动部署（不使用 Docker）

### 手动部署

1. **安装依赖**
```bash
cd advanced
composer install
```

2. **初始化应用**
```bash
php init
# 选择开发环境 (0) 或生产环境 (1)
```

3. **配置数据库**

编辑 `files/common/config/main-local.php`:
```php
'db' => [
    'dsn' => 'mysql:host=localhost;dbname=your_database',
    'username' => 'your_username',
    'password' => 'your_password',
],
```

4. **运行迁移**
```bash
php yii migrate
```

5. **初始化 RBAC**
```bash
php yii rbac/init
```

6. **配置 Web 服务器**

参考 `docker/api-default.conf` 配置 Nginx 或 Apache。

## 🐳 Docker 部署

### 快速参考

| 文档 | 说明 |
|------|------|
| [快速启动指南](DOCKER_QUICK_START.md) | 最常用的命令和操作 ⭐ |
| [完整中文文档](docker/README.zh-CN.md) | 详细的使用说明和故障排查 |
| [配置完成说明](DOCKER_SETUP_COMPLETE.md) | Docker 环境配置详情 |

### 服务端口

| 服务 | 端口 | 说明 |
|------|------|------|
| API 服务 | 8081 | 主 API 接口 |
| 后台应用 | 8082 | 后台管理系统 |
| phpMyAdmin | 8080 | 数据库管理工具 |
| MySQL | 3306 | 数据库服务 |
| Redis | 6379 | 缓存服务 |

### 常用命令

```bash
# 使用 Makefile（推荐）
make help           # 查看所有可用命令
make start          # 启动所有服务
make stop           # 停止服务
make logs           # 查看日志
make shell          # 进入 API 容器
make migrate        # 运行数据库迁移
make test           # 运行测试
make db-backup      # 备份数据库

# 使用脚本
./start-docker.sh   # 一键启动（自动初始化）
./stop-docker.sh    # 停止服务
./check-env.sh      # 检查环境配置

# 使用 docker-compose
docker-compose up -d              # 启动服务
docker-compose down               # 停止服务
docker-compose logs -f api        # 查看 API 日志
docker-compose exec api bash      # 进入 API 容器
docker-compose restart            # 重启服务
```

### 环境配置

首次使用需要配置环境变量：

```bash
# 1. 复制环境配置模板
cp .env.docker.example .env.docker

# 2. 编辑配置文件
# 填入数据库密码、邮箱配置、云服务密钥等

# 3. 生成 JWT 密钥
mkdir -p jwt_keys
openssl ecparam -genkey -name prime256v1 -noout -out jwt_keys/jwt-key.pem
```

或者直接运行一键启动脚本，它会自动处理这些步骤：

```bash
./start-docker.sh
```

## 📖 API 文档

### Swagger UI

项目集成了完整的 OpenAPI 3.0 文档系统：

- **访问地址**: `http://your-domain/swagger`
- **JSON Schema**: `http://your-domain/swagger/json-schema`

### 认证方式

Swagger 文档使用 HTTP Basic Authentication 保护：

**默认凭据**:
- 用户名: `swagger_admin`
- 密码: `YourStrongP@ssw0rd!`

**环境变量配置**:
```bash
export SWAGGER_USERNAME=your_username
export SWAGGER_PASSWORD=your_password
export SWAGGER_ENABLED=true
```

### API 版本

- **V1 API**: `/v1/*` - 主要 API 版本

### 主要端点

#### 认证相关
- `POST /v1/auth/login` - 用户登录
- `POST /v1/auth/refresh` - 刷新 Token
- `POST /v1/site/apple-id` - Apple ID 认证
- `POST /v1/wechat/login` - 微信登录

#### 用户管理
- `GET /v1/user/info` - 获取用户信息
- `PUT /v1/user/update` - 更新用户信息

#### 资源管理
- `GET /v1/resource` - 获取资源列表
- `POST /v1/resource` - 创建资源
- `GET /v1/meta` - 获取 Meta 列表
- `GET /v1/prefab` - 获取 Prefab 列表

#### 教育管理
- `GET /v1/edu-school` - 学校管理
- `GET /v1/edu-class` - 班级管理
- `GET /v1/edu-teacher` - 教师管理
- `GET /v1/edu-student` - 学生管理

#### 群组系统
- `POST /v1/group/join` - 加入群组
- `GET /v1/group/{id}/verses` - 获取群组内容

更多详细信息请查看 [Swagger 文档](docs/SWAGGER_CONFIG.md)。

## 📁 项目结构

```
.
├── advanced/                    # Yii2 应用主目录
│   ├── api/                    # API 应用
│   │   ├── controllers/        # 根控制器
│   │   │   └── SwaggerController.php
│   │   ├── modules/           # 模块目录
│   │   │   └── v1/           # V1 API 模块
│   │   │       ├── controllers/  # V1 控制器
│   │   │       ├── models/      # V1 模型
│   │   │       └── components/  # V1 组件
│   │   ├── web/               # Web 资源
│   │   │   └── swagger-ui/   # Swagger UI 静态文件
│   │   └── config/            # API 配置
│   ├── backend/               # 后台管理应用
│   ├── common/                # 共享代码
│   │   ├── config/           # 共享配置
│   │   ├── models/           # 共享模型
│   │   └── components/       # 共享组件
│   ├── console/              # 控制台应用
│   │   └── migrations/       # 数据库迁移
│   └── vendor/               # Composer 依赖
├── docker/                    # Docker 配置
│   ├── Dockerfile
│   ├── docker-compose.yml
│   ├── init.sql              # 初始化 SQL
│   └── *.conf                # Nginx 配置
├── docs/                      # 项目文档
│   ├── SWAGGER_CONFIG.md     # Swagger 配置文档
│   ├── SWAGGER_DEPLOYMENT.md # 部署文档
│   └── OPENAPI_CONTROLLERS_STATUS.md
├── files/                     # 配置文件模板
│   ├── api/config/           # API 配置模板
│   ├── backend/config/       # 后台配置模板
│   └── common/config/        # 共享配置模板
└── .kiro/                     # Kiro 规范文档
    └── specs/                # 功能规范
```

## 🔧 开发指南

### 添加新的 API 端点

1. **创建控制器**

在 `advanced/api/modules/v1/controllers/` 创建新控制器：

```php
<?php
namespace api\modules\v1\controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Example", description="示例接口")
 */
class ExampleController extends \yii\rest\ActiveController
{
    public $modelClass = 'api\modules\v1\models\Example';
    
    /**
     * @OA\Get(
     *     path="/v1/example",
     *     summary="获取示例列表",
     *     tags={"Example"},
     *     security={{"Bearer": {}}},
     *     @OA\Response(response=200, description="成功")
     * )
     */
    public function actionIndex()
    {
        return parent::actionIndex();
    }
}
```

2. **配置路由**

在 `files/api/config/main.php` 添加路由：

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'v1/example',
],
```

3. **更新 Swagger 扫描**

在 `SwaggerController.php` 的 `actionJsonSchema()` 方法中添加新文件到扫描列表。

### 数据库迁移

**创建迁移**:
```bash
php yii migrate/create create_example_table
```

**运行迁移**:
```bash
php yii migrate
```

**回滚迁移**:
```bash
php yii migrate/down
```

### 测试

**运行所有测试**:
```bash
vendor/bin/codecept run
```

**运行单元测试**:
```bash
vendor/bin/codecept run unit
```

**运行功能测试**:
```bash
vendor/bin/codecept run functional
```

### 邮件功能测试

项目集成了完整的邮件发送功能，支持验证码、密码重置、邮箱验证等场景。

**测试所有邮件类型**:
```bash
docker exec -it api7dgamecom-api-1 php yii email-test/all your@email.com
```

**测试单个邮件类型**:
```bash
# 验证码邮件
docker exec -it api7dgamecom-api-1 php yii email-test/verification-code your@email.com

# 密码重置邮件
docker exec -it api7dgamecom-api-1 php yii email-test/password-reset your@email.com

# 邮箱验证邮件
docker exec -it api7dgamecom-api-1 php yii email-test/email-verify your@email.com

# 简单测试邮件
docker exec -it api7dgamecom-api-1 php yii email-test/simple your@email.com
```

**邮件功能特性**:
- ✅ 使用 Symfony Mailer 4.0（最新版本）
- ✅ 支持腾讯企业邮箱
- ✅ HTML 和纯文本双格式
- ✅ 响应式邮件模板
- ✅ 安全的 SMTP 授权码认证

📖 **详细文档**: [邮件功能使用指南](EMAIL_FUNCTIONALITY_GUIDE.md)

### 代码规范

项目遵循 PSR-12 编码规范。

## 🔒 安全配置

### JWT Token

JWT Token 配置在 `files/api/config/params.php`:

```php
'jwt' => [
    'issuer' => 'your-app',
    'audience' => 'your-app',
    'id' => 'unique-id',
    'expire' => 3600, // 1 hour
],
```

### CORS 配置

CORS 配置在 `files/api/config/main.php`:

```php
'as cors' => [
    'class' => \yii\filters\Cors::class,
    'cors' => [
        'Origin' => ['*'],
        'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        'Access-Control-Request-Headers' => ['*'],
    ],
],
```

### 生产环境建议

1. **禁用 Debug 模式**
```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');
```

2. **使用强密码**
- 数据库密码至少 16 字符
- Swagger 访问密码至少 16 字符
- JWT 密钥使用随机生成的强密钥

3. **启用 HTTPS**
- 生产环境必须使用 HTTPS
- 配置 SSL 证书

4. **限制 Swagger 访问**
```bash
export SWAGGER_ENABLED=false
```

或使用 IP 白名单限制访问。

## 📚 文档

- [Swagger 配置指南](docs/SWAGGER_CONFIG.md)
- [部署文档](docs/SWAGGER_DEPLOYMENT.md)
- [API 健康检查](docs/API_HEALTH_VERSION.md)
- [OpenAPI 控制器状态](docs/OPENAPI_CONTROLLERS_STATUS.md)
- [邮件功能使用指南](EMAIL_FUNCTIONALITY_GUIDE.md) ⭐
- [邮件测试结果](EMAIL_TEST_RESULTS.md)
- [获取 SMTP 授权码](GET_SMTP_AUTH_CODE.md)
- [邮件配置指南](EMAIL_CONFIG_GUIDE.md)

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

### 开发流程

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 📄 许可证

本项目采用 BSD-3-Clause 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 🙏 致谢

- [Yii Framework](https://www.yiiframework.com/) - 优秀的 PHP 框架
- [Swagger PHP](https://github.com/zircote/swagger-php) - OpenAPI 文档生成
- [JWT](https://jwt.io/) - JSON Web Token 标准

## 📞 联系方式

如有问题或建议，请通过以下方式联系：

- 提交 [Issue](../../issues)
- 发送邮件至: your-email@example.com

---

<p align="center">
  Made with ❤️ by Your Team
</p>

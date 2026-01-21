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
  <a href="#开发指南">开发指南</a>
</p>

---

## 📋 项目简介

这是一个基于 Yii2 Advanced 模板构建的企业级 RESTful API 后端系统，提供完整的用户认证、资源管理、教育管理等功能模块。项目采用模块化设计，支持多版本 API，集成了完整的 OpenAPI 3.0 文档系统。

## ✨ 主要特性

### 核心功能
- 🔐 **JWT 认证系统** - 基于 JWT Token 的用户认证和授权
- 👥 **用户管理** - 完整的用户注册、登录、信息管理
- 🍎 **Apple ID 集成** - 支持 Apple ID 第三方登录
- 💬 **微信集成** - 微信登录和支付功能
- 📁 **文件管理** - 文件上传、存储和管理
- 🏷️ **标签系统** - 灵活的标签分类管理

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
- MySQL >= 5.7
- Redis (可选)
- Composer
- Docker & Docker Compose (推荐)

### 使用 Docker 部署（推荐）

1. **克隆项目**
```bash
git clone <repository-url>
cd yii2-backend
```

2. **配置环境变量**
```bash
cp advanced/.env.example advanced/.env
# 编辑 .env 文件，配置数据库和其他服务
```

3. **启动 Docker 容器**
```bash
docker-compose up -d
```

4. **安装依赖**
```bash
docker-compose exec php composer install
```

5. **初始化应用**
```bash
docker-compose exec php php init
# 选择开发环境 (0) 或生产环境 (1)
```

6. **运行数据库迁移**
```bash
docker-compose exec php php yii migrate
```

7. **访问应用**
- API 地址: `http://localhost:81`
- Swagger 文档: `http://localhost:81/swagger`

### 手动部署

1. **安装依赖**
```bash
cd advanced
composer install
```

2. **初始化应用**
```bash
php init
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

5. **配置 Web 服务器**

参考 `docker/api-default.conf` 配置 Nginx 或 Apache。

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

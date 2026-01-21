# Swagger API 文档配置说明

本文档描述了项目中 Swagger/OpenAPI 的配置方式以及常见问题的解决方案。

## 目录

- [快速开始（新项目配置）](#快速开始新项目配置)
- [访问地址](#访问地址)
- [推荐文件结构](#推荐文件结构)
- [如何添加 API 注解](#如何添加-api-注解)
- [常见问题与解决方案](#常见问题与解决方案)
- [依赖说明](#依赖说明)

---

## 快速开始（新项目配置）

> [!NOTE]
> 本节适用于在新的 Yii2 项目中从零开始配置 Swagger API 文档。

### 步骤 1: 安装 Composer 依赖

```bash
cd your-project/api

# 安装 swagger-php 库
composer require zircote/swagger-php:^4.0

# 安装 doctrine/annotations（swagger-php 依赖）
composer require doctrine/annotations:^2.0
```

### 步骤 2: 下载 Swagger UI 静态资源

从 [Swagger UI GitHub Releases](https://github.com/swagger-api/swagger-ui/releases) 下载最新版本。

只需要以下两个文件：

```bash
mkdir -p api/web/swagger-ui

# 下载核心文件（以 v5.x 为例）
curl -L https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js -o api/web/swagger-ui/swagger-ui-bundle.js
curl -L https://unpkg.com/swagger-ui-dist@5/swagger-ui.css -o api/web/swagger-ui/swagger-ui.css
```

或者手动下载并放置到 `api/web/swagger-ui/` 目录：

| 文件                   | 说明                        |
| ---------------------- | --------------------------- |
| `swagger-ui-bundle.js` | 核心 JavaScript 库 (~1.4MB) |
| `swagger-ui.css`       | 样式表 (~150KB)             |

### 步骤 3: 创建 SwaggerController

在 `api/controllers/` 目录下创建 `SwaggerController.php`：

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use OpenApi\Generator;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="你的 API 标题",
 *     description="API 文档描述"
 * )
 * @OA\Server(url="/", description="API Server")
 * @OA\SecurityScheme(
 *     securityScheme="Bearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * 渲染 Swagger UI 界面
     */
    public function actionIndex()
    {
        $swaggerUiUrl = Yii::$app->request->baseUrl . '/swagger-ui';
        $jsonSchemaUrl = Yii::$app->urlManager->createUrl(['swagger/json-schema']);

        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API 文档 - Swagger UI</title>
    <link rel="stylesheet" href="{$swaggerUiUrl}/swagger-ui.css">
    <style>
        body { margin: 0; padding: 0; }
        .swagger-ui .topbar { display: none; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{$swaggerUiUrl}/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function() {
            SwaggerUIBundle({
                url: "{$jsonSchemaUrl}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>
HTML;
    }

    /**
     * 生成 OpenAPI JSON Schema
     */
    public function actionJsonSchema()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $baseDir = dirname(__DIR__); // api/

        // 配置需要扫描的文件列表
        $scanFiles = [
            $baseDir . '/controllers/SwaggerController.php',
            // 添加你的 Controllers
            // $baseDir . '/controllers/YourController.php',
            // $baseDir . '/modules/v2/controllers/ExampleController.php',

            // 添加你的 Models
            // $baseDir . '/models/YourModel.php',
        ];

        // 文件存在性检查
        $existingFiles = array_filter($scanFiles, 'file_exists');

        if (empty($existingFiles)) {
            return [
                'error' => 'No files found to scan',
                'baseDir' => $baseDir,
                'attempted' => $scanFiles
            ];
        }

        $openapi = Generator::scan($existingFiles, ['validate' => false]);

        return json_decode($openapi->toJson());
    }
}
```

### 步骤 4: 配置路由

在 `config/web.php` 的 `urlManager` 规则中添加：

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [
        // Swagger API 文档路由
        'GET swagger' => 'swagger/index',
        'GET swagger/json-schema' => 'swagger/json-schema',

        // ... 其他路由
    ],
],
```

### 步骤 5: 验证安装

1. 重启 Web 服务器或清除缓存
2. 访问 `http://your-domain/swagger`
3. 应该能看到 Swagger UI 界面

> [!TIP]
> 如果看到空白页或错误，检查浏览器控制台和网络请求，确认 JSON Schema 是否正确加载。

### 步骤 6: 添加你的第一个 API 注解

在任意 Controller 中添加注解：

```php
<?php
namespace app\controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Test", description="测试接口")
 */
class TestController extends \yii\rest\Controller
{
    /**
     * @OA\Get(
     *     path="/test",
     *     summary="测试接口",
     *     tags={"Test"},
     *     @OA\Response(response=200, description="成功")
     * )
     */
    public function actionIndex()
    {
        return ['message' => 'Hello World'];
    }
}
```

然后将该文件添加到 `SwaggerController.php` 的 `$scanFiles` 数组中。

---

## 访问地址

配置完成后，可通过以下地址访问：

| 地址                   | 说明                |
| ---------------------- | ------------------- |
| `/swagger`             | Swagger UI 界面     |
| `/swagger/json-schema` | OpenAPI JSON Schema |

### 路由配置

在 `config/web.php` 的 `urlManager` 规则中添加：

```php
// Swagger API 文档路由
'GET swagger' => 'swagger/index',
'GET swagger/json-schema' => 'swagger/json-schema',
```

---

## 推荐文件结构

```
api/
├── controllers/
│   └── SwaggerController.php       # Swagger 主控制器
├── modules/v1/                     # 或 v2 等版本目录
│   ├── controllers/               # 需要添加注解的控制器
│   │   ├── YourController.php
│   │   └── ...
│   └── models/                    # 需要添加注解的模型
│       ├── YourModel.php
│       └── ...
├── web/swagger-ui/                 # Swagger UI 静态资源
│   ├── swagger-ui-bundle.js
│   └── swagger-ui.css
└── vendor/zircote/swagger-php/     # Swagger PHP 库（Composer 安装）
```

---

## 如何添加 API 注解

### Controller 注解示例

为 Controller 添加 Tag 和 API 操作注解：

```php
<?php
namespace app\modules\v2\controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Device",
 *     description="设备管理相关接口"
 * )
 */
class DeviceController extends ActiveController
{
    /**
     * @OA\Get(
     *     path="/v2/devices",
     *     summary="获取设备列表",
     *     tags={"Device"},
     *     security={{"Bearer": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功返回设备列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Device")
     *         )
     *     )
     * )
     */
    public function actionIndex()
    {
        // ...
    }
}
```

### Model 注解示例

为 Model 添加 Schema 注解：

```php
<?php
namespace app\modules\v2\models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Device",
 *     title="设备",
 *     description="设备模型",
 *     @OA\Property(property="id", type="integer", description="设备ID"),
 *     @OA\Property(property="name", type="string", description="设备名称"),
 *     @OA\Property(property="status", type="integer", description="设备状态"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="创建时间")
 * )
 */
class Device extends \yii\db\ActiveRecord
{
    // ...
}
```

---

## 常见问题与解决方案

### 问题 1: Mixed-Content 错误 (HTTP/HTTPS)

**问题描述：**

当网站使用 HTTPS 访问时，Swagger UI 尝试从 HTTP 地址获取 JSON Schema，导致浏览器阻止请求并显示错误：

```
Failed to fetch http://x.4mr.cn/swagger/json-schema
Possible mixed-content issue
```

**解决方案：**

修改 `SwaggerController.php`，动态检测当前协议并生成正确的 URL：

```php
public function actionIndex()
{
    $swaggerUiUrl = Yii::$app->request->baseUrl . '/swagger-ui';

    // 动态获取协议，使用 urlManager 创建相对路径 URL
    $jsonSchemaUrl = Yii::$app->urlManager->createUrl(['swagger/json-schema']);

    // ... 渲染 HTML
}
```

> [!TIP]
> 使用 `createUrl()` 生成相对路径而非绝对路径，可以避免 mixed-content 问题。

---

### 问题 2: Swagger 端点安全保护

**问题描述：**

默认情况下，Swagger UI 是公开访问的，可能会暴露 API 结构。

**解决方案：**

有两种方案可以保护 Swagger 端点：

---

#### 方案 A: PHP 应用层保护（推荐）

这种方案在应用层实现认证，无需修改 Web 服务器配置，更便于跨环境部署。

**步骤 1: 配置 params.php**

在 `config/params.php` 中添加 Swagger 凭据配置（支持环境变量）：

```php
<?php
return [
    // ... 其他配置 ...

    // Swagger API 文档访问凭据（从环境变量读取）
    'swagger' => [
        'username' => getenv('SWAGGER_USERNAME') ?: 'swagger_admin',
        'password' => getenv('SWAGGER_PASSWORD') ?: 'YourStrongP@ssw0rd!',
    ],
];
```

**步骤 2: 修改 SwaggerController.php**

在 `SwaggerController` 中添加 `beforeAction` 方法实现 HTTP Basic Authentication：

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use OpenApi\Generator;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(...)
 * @OA\Server(...)
 * @OA\SecurityScheme(...)
 */
class SwaggerController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * 在执行任何操作前进行身份验证
     * 使用 HTTP Basic Authentication 保护 Swagger 文档
     * 凭据配置在 config/params.php 的 'swagger' 键中
     */
    public function beforeAction($action)
    {
        // 从配置文件读取凭据
        $swaggerConfig = Yii::$app->params['swagger'] ?? null;

        if (!$swaggerConfig) {
            throw new \yii\web\ServerErrorHttpException('Swagger 配置未找到');
        }

        // 检查 HTTP Basic Auth 凭据
        $username = $_SERVER['PHP_AUTH_USER'] ?? null;
        $password = $_SERVER['PHP_AUTH_PW'] ?? null;

        if ($username !== $swaggerConfig['username'] || $password !== $swaggerConfig['password']) {
            header('WWW-Authenticate: Basic realm="Swagger API Documentation"');
            header('HTTP/1.0 401 Unauthorized');
            echo '需要认证才能访问 API 文档';
            exit;
        }

        return parent::beforeAction($action);
    }

    // ... actionIndex() 和 actionJsonSchema() 方法保持不变
}
```

**步骤 3: Docker Compose 集成**

在 `docker-compose.yml` 中添加环境变量：

```yaml
services:
  php:
    # ... 其他配置 ...
    environment:
      # Swagger 认证凭据
      SWAGGER_USERNAME: ${SWAGGER_USERNAME:-swagger_admin}
      SWAGGER_PASSWORD: ${SWAGGER_PASSWORD:-YourStrongP@ssw0rd!}
```

或在 `.env` 文件中配置：

```env
SWAGGER_USERNAME=swagger_admin
SWAGGER_PASSWORD=Pr0duct10n$ecureP@ss!
```

**强密码建议：**

生成强密码应包含：

- ✅ 大写字母
- ✅ 小写字母
- ✅ 数字
- ✅ 特殊符号 (`@`, `#`, `!`, `$`)
- ✅ 长度至少 16 字符

**不同环境配置示例：**

| 环境     | 用户名          | 密码强度                |
| -------- | --------------- | ----------------------- |
| 开发环境 | `dev`           | 简单密码即可            |
| 测试环境 | `swagger_test`  | 中等强度                |
| 生产环境 | `swagger_admin` | 强密码（建议 20+ 字符） |

---

#### 方案 B: Nginx 层保护

如果偏好在 Web 服务器层面处理认证：

```nginx
location /swagger {
    auth_basic "API Documentation";
    auth_basic_user_file /path/to/.htpasswd;

    try_files $uri $uri/ /index.php?$args;
}
```

生成 `.htpasswd` 文件：

```bash
htpasswd -c /path/to/.htpasswd username
```

---

> [!IMPORTANT]
> 建议在生产环境中启用认证保护，防止 API 文档被未授权访问。
>
> **方案对比：**
>
> - **方案 A (PHP 应用层)**：更灵活，支持环境变量，便于 Docker 部署
> - **方案 B (Nginx 层)**：性能更好，但需要修改服务器配置

---

### 问题 3: 扫描文件找不到

**问题描述：**

Swagger 扫描返回 "No files found to scan" 错误。

**解决方案：**

1. 检查文件路径是否正确
2. 确保所有配置的文件都存在
3. 检查 `SwaggerController.php` 中的扫描文件列表

```php
// actionJsonSchema() 中的调试代码
if (empty($existingFiles)) {
    return [
        'error' => 'No files found to scan',
        'baseDir' => $baseDir,
        'attempted' => $scanFiles
    ];
}
```

---

### 问题 4: 新增 Controller 后 API 不显示

**问题描述：**

添加了新的 Controller 但 Swagger 中看不到相关 API。

**解决方案：**

1. 确保在 `SwaggerController.php` 的 `$scanFiles` 数组中添加新文件

```php
$scanFiles = [
    // ... 现有文件
    $baseDir . '/modules/v2/controllers/NewController.php',  // 添加新文件
];
```

2. 确保 Controller 中添加了正确的 `@OA\Tag` 和 `@OA\Get/Post/Put/Delete` 注解

3. 刷新 `/swagger/json-schema` 确认 JSON 已更新

---

### 问题 5: CSRF 验证失败

**问题描述：**

访问 Swagger 相关接口时出现 CSRF 验证错误。

**解决方案：**

在 `SwaggerController` 中已禁用 CSRF 验证：

```php
class SwaggerController extends Controller
{
    public $enableCsrfValidation = false;
    // ...
}
```

---

## 依赖说明

### Composer 依赖

项目使用以下 Swagger 相关包：

```json
{
  "require": {
    "zircote/swagger-php": "^4.0"
  }
}
```

### 前端资源

Swagger UI 静态文件位于 `api/web/swagger-ui/` 目录：

- `swagger-ui-bundle.js` - Swagger UI 核心 JS 库
- `swagger-ui.css` - Swagger UI 样式表

---

## 维护说明

### 添加新的 API 端点

1. 在对应的 Controller 中添加 OpenAPI 注解
2. 如果是新 Controller，需要在 `SwaggerController.php` 中添加到扫描列表
3. 如果有新的数据模型，同样需要添加到扫描列表并添加 `@OA\Schema` 注解

### 更新 API 信息

修改 `SwaggerController.php` 头部的 `@OA\Info` 注解：

```php
/**
 * @OA\Info(
 *     version="2.0.0",        // 更新版本号
 *     title="API 标题",        // 更新标题
 *     description="API 描述"   // 更新描述
 * )
 */
```

---

## 参考链接

- [swagger-php 官方文档](https://zircote.github.io/swagger-php/)
- [OpenAPI 3.0 规范](https://swagger.io/specification/)
- [Swagger UI 官方文档](https://swagger.io/tools/swagger-ui/)

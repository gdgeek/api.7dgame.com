# Swagger API 文档部署指南

## 访问地址

配置完成后，可通过以下地址访问：

| 地址 | 说明 |
|------|------|
| `/swagger` | Swagger UI 界面 |
| `/swagger/json-schema` | OpenAPI JSON Schema |

## 认证凭据

Swagger 文档使用 HTTP Basic Authentication 保护。

### 开发环境

默认凭据（在 `files/api/config/params.php` 中配置）：
- 用户名: `swagger_admin`
- 密码: `YourStrongP@ssw0rd!`

### 使用环境变量

推荐使用环境变量配置凭据：

```bash
# 设置环境变量
export SWAGGER_USERNAME=your_username
export SWAGGER_PASSWORD=your_password
export SWAGGER_ENABLED=true
```

或在 `.env` 文件中配置：

```env
SWAGGER_USERNAME=swagger_admin
SWAGGER_PASSWORD=YourStrongP@ssw0rd!
SWAGGER_ENABLED=true
```

### 生产环境

**重要**: 生产环境必须使用强密码或禁用 Swagger 访问。

强密码要求：
- 至少 16 个字符
- 包含大写字母、小写字母、数字和特殊符号
- 示例: `Pr0duct10n$ecureP@ss!2024`

禁用 Swagger（推荐）：

```env
SWAGGER_ENABLED=false
```

## 访问流程

1. 访问 `http://your-domain/swagger`
2. 浏览器会弹出认证对话框
3. 输入用户名和密码
4. 认证成功后即可查看 API 文档

## 使用 Swagger UI 测试 API

### 1. 获取 JWT Token

首先调用登录接口获取 Token：

```bash
curl -X POST http://your-domain/site/login \
  -H "Content-Type: application/json" \
  -d '{"username":"your_username","password":"your_password"}'
```

响应示例：

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "id": 1,
  "username": "admin"
}
```

### 2. 在 Swagger UI 中配置 Token

1. 点击页面右上角的 "Authorize" 按钮
2. 在弹出的对话框中输入: `Bearer <your_token>`
3. 点击 "Authorize" 确认
4. 点击 "Close" 关闭对话框

### 3. 测试需要认证的接口

现在可以直接在 Swagger UI 中测试需要认证的接口，Token 会自动包含在请求头中。

## 故障排查

### 问题 1: 无法访问 Swagger UI

**症状**: 访问 `/swagger` 返回 404

**解决方案**:
1. 检查路由配置是否正确（`files/api/config/main.php`）
2. 确认 Web 服务器配置支持 URL 重写
3. 清除应用缓存

### 问题 2: 认证失败

**症状**: 输入用户名密码后仍然提示认证

**解决方案**:
1. 检查 `files/api/config/params.php` 中的凭据配置
2. 确认环境变量是否正确设置
3. 检查浏览器是否缓存了旧的认证信息（清除浏览器缓存）

### 问题 3: JSON Schema 为空或错误

**症状**: Swagger UI 显示但没有 API 端点

**解决方案**:
1. 访问 `/swagger/json-schema` 查看原始 JSON
2. 检查是否有错误信息
3. 确认控制器和模型文件路径正确
4. 检查 OpenAPI 注解语法是否正确

### 问题 4: Mixed-Content 错误

**症状**: HTTPS 网站无法加载 Swagger UI

**解决方案**:
- 已在代码中使用相对路径解决，无需额外配置
- 如果仍有问题，检查 `urlManager->createUrl()` 是否正常工作

### 问题 5: CORS 错误

**症状**: 跨域请求被阻止

**解决方案**:
- 检查 `files/api/config/main.php` 中的 CORS 配置
- 确认 `Access-Control-Allow-Origin` 设置正确

## 添加新的 API 端点

### 1. 在控制器中添加注解

```php
/**
 * @OA\Get(
 *     path="/your/endpoint",
 *     summary="端点描述",
 *     tags={"YourTag"},
 *     security={{"Bearer": {}}},
 *     @OA\Response(response=200, description="成功")
 * )
 */
public function actionYourAction()
{
    // 实现代码
}
```

### 2. 更新扫描文件列表

在 `SwaggerController.php` 的 `actionJsonSchema()` 方法中添加新文件：

```php
$scanFiles = [
    // ... 现有文件
    $baseDir . '/controllers/YourController.php',
];
```

### 3. 刷新文档

访问 `/swagger/json-schema` 确认新端点已包含，然后在 Swagger UI 中查看。

## 安全建议

1. **生产环境**: 强烈建议禁用 Swagger 或使用 IP 白名单限制访问
2. **密码管理**: 定期更换 Swagger 访问密码
3. **HTTPS**: 生产环境必须使用 HTTPS
4. **日志监控**: 监控 Swagger 访问日志，发现异常访问及时处理

## 维护

### 更新 API 文档

1. 修改控制器或模型中的 OpenAPI 注解
2. 刷新浏览器查看更新（无需重启服务）

### 更新 Swagger UI 版本

1. 下载新版本的 `swagger-ui-bundle.js` 和 `swagger-ui.css`
2. 替换 `advanced/api/web/swagger-ui/` 目录中的文件
3. 清除浏览器缓存

## 参考资源

- [OpenAPI 3.0 规范](https://swagger.io/specification/)
- [swagger-php 文档](https://zircote.github.io/swagger-php/)
- [Swagger UI 文档](https://swagger.io/tools/swagger-ui/)
- 项目配置文档: `docs/SWAGGER_CONFIG.md`

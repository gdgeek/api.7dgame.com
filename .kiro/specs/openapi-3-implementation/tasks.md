# Implementation Plan: OpenAPI 3.0 Implementation

## Overview

本实现计划将 OpenAPI 3.0 文档系统集成到 Yii2 Advanced 项目中。实现将分为五个主要阶段：基础设施搭建、核心功能实现、安全保护、注解标注和测试验证。每个任务都引用了相应的需求条款，确保完整的需求追溯性。

## Tasks

- [x] 1. 搭建基础设施
  - [x] 1.1 验证 Composer 依赖
    - 确认 `zircote/swagger-php: ^4.0` 已安装
    - 确认 `doctrine/annotations: ^2.0` 已安装
    - 如果缺失，更新 composer.json 并运行 `composer install`
    - _Requirements: 1.1, 1.2_

  - [x] 1.2 下载并配置 Swagger UI 静态资源
    - 创建目录 `advanced/api/web/swagger-ui/`
    - 下载 `swagger-ui-bundle.js` 到该目录
    - 下载 `swagger-ui.css` 到该目录
    - 验证文件大小（bundle.js ~1.4MB, css ~150KB）
    - _Requirements: 5.1, 5.2_

  - [x] 1.3 配置路由规则
    - 在 `files/api/config/main.php` 的 urlManager 中添加 Swagger 路由
    - 添加 `'GET swagger' => 'swagger/index'`
    - 添加 `'GET swagger/json-schema' => 'swagger/json-schema'`
    - _Requirements: 5.1_

- [x] 2. 创建 SwaggerController
  - [x] 2.1 创建控制器文件和基本结构
    - 在 `advanced/api/controllers/` 创建 `SwaggerController.php`
    - 继承 `yii\web\Controller`
    - 设置 `$enableCsrfValidation = false`
    - _Requirements: 1.1, 5.1_

  - [x] 2.2 添加全局 OpenAPI 注解
    - 在 SwaggerController 类注释中添加 @OA\Info 注解（版本、标题、描述）
    - 添加 @OA\Server 注解（url="/"）
    - 添加 @OA\SecurityScheme 注解（Bearer JWT）
    - _Requirements: 4.1, 4.2_

  - [x] 2.3 实现 actionIndex() 方法
    - 生成 Swagger UI HTML 页面
    - 使用 `Yii::$app->request->baseUrl` 构建静态资源 URL
    - 使用 `Yii::$app->urlManager->createUrl()` 生成 JSON Schema URL（避免 mixed-content）
    - 配置 SwaggerUIBundle 初始化参数
    - _Requirements: 5.1, 5.2_

  - [x] 2.4 实现 actionJsonSchema() 方法
    - 设置响应格式为 JSON
    - 配置需要扫描的文件列表（controllers 和 models）
    - 实现文件存在性检查
    - 使用 `OpenApi\Generator::scan()` 生成 OpenAPI 文档
    - 返回 JSON 格式的 OpenAPI 规范
    - 添加错误处理（文件未找到、生成失败）
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [ ]* 2.5 编写 SwaggerController 单元测试
    - 测试 actionIndex() 返回正确的 HTML 结构
    - 测试 actionJsonSchema() 返回有效的 JSON
    - 测试文件扫描错误处理
    - _Requirements: 1.2, 1.3, 1.4_

- [x] 3. 实现 HTTP Basic Authentication 保护
  - [x] 3.1 配置认证参数
    - 在 `advanced/api/config/params.php` 添加 swagger 配置节
    - 配置 username（支持环境变量 SWAGGER_USERNAME）
    - 配置 password（支持环境变量 SWAGGER_PASSWORD）
    - 配置 enabled 开关（支持环境变量 SWAGGER_ENABLED）
    - _Requirements: 10.2_

  - [x] 3.2 实现 beforeAction() 认证逻辑
    - 检查 swagger 配置是否存在
    - 检查 enabled 开关，禁用时返回 404
    - 读取 PHP_AUTH_USER 和 PHP_AUTH_PW
    - 验证用户名和密码
    - 认证失败时发送 WWW-Authenticate 头和 401 响应
    - _Requirements: 10.1, 10.3, 10.5_

  - [ ]* 3.3 编写认证功能测试
    - 测试正确凭据可以访问
    - 测试错误凭据返回 401
    - 测试缺失凭据返回 401
    - 测试禁用状态返回 404
    - _Requirements: 10.3, 10.5_

- [ ] 4. Checkpoint - 验证基础功能
  - 启动开发服务器
  - 访问 `/swagger` 确认 UI 加载
  - 访问 `/swagger/json-schema` 确认 JSON 生成
  - 测试认证保护是否生效
  - 确保所有测试通过，如有问题请询问用户

- [x] 5. 为现有控制器添加 OpenAPI 注解
  - [x] 5.1 为 SiteController 添加注解
    - 添加 @OA\Tag 注解（name="Authentication"）
    - 为 actionLogin() 添加 @OA\Post 注解
    - 定义请求体 Schema（username, password）
    - 定义成功响应（200，包含 token 和用户信息）
    - 定义错误响应（400, 401）
    - 为 actionHealth() 添加 @OA\Get 注解
    - 为 actionVersion() 添加 @OA\Get 注解
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.6, 4.4_

  - [x] 5.2 为 ServerController 添加注解
    - 添加 @OA\Tag 注解（name="Server"）
    - 为 actionUser() 添加 @OA\Get 注解（需要 JWT 认证）
    - 为 actionLogout() 添加 @OA\Post 注解
    - 为 actionToken() 添加 @OA\Post 注解（Token 刷新）
    - 所有端点添加 security={{"Bearer": {}}}
    - _Requirements: 2.1, 2.2, 2.5, 4.3, 4.5_

  - [x] 5.3 为 v1/UserController 添加注解
    - 添加 @OA\Tag 注解（name="User"）
    - 为 actionInfo() 添加 @OA\Get 注解
    - 为 actionUpdate() 添加 @OA\Post 注解
    - 定义请求和响应 Schema
    - 添加 security={{"Bearer": {}}}
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 6.1_

  - [x] 5.4 更新 SwaggerController 扫描文件列表
    - 将 SiteController.php 添加到 $scanFiles
    - 将 ServerController.php 添加到 $scanFiles
    - 将 v1/UserController.php 添加到 $scanFiles
    - _Requirements: 1.3, 6.1_

- [x] 6. 为数据模型添加 Schema 注解
  - [x] 6.1 为 User 模型添加 Schema
    - 添加 @OA\Schema 注解（schema="User"）
    - 定义属性：id, username, nickname, roles
    - 为每个属性添加类型、格式和描述
    - 添加示例数据
    - _Requirements: 3.1, 3.2, 3.6_

  - [x] 6.2 为 UserInfo 模型添加 Schema
    - 添加 @OA\Schema 注解（schema="UserInfo"）
    - 定义所有公开属性
    - 添加属性描述和示例
    - _Requirements: 3.1, 3.2, 3.6_

  - [x] 6.3 创建标准错误响应 Schema
    - 在 SwaggerController 或单独文件中定义 ErrorResponse Schema
    - 包含字段：name, message, code, status
    - 为每个字段添加类型和示例
    - _Requirements: 8.1, 8.2_

  - [x] 6.4 更新 SwaggerController 扫描文件列表
    - 将 User.php 添加到 $scanFiles
    - 将 UserInfo.php 添加到 $scanFiles
    - 将 ErrorResponse 定义文件添加到 $scanFiles
    - _Requirements: 1.4_

  - [ ]* 6.5 编写 Schema 验证测试
    - 测试生成的文档包含所有定义的 Schema
    - 测试 Schema 结构完整性
    - _Requirements: 1.4, 3.1_

- [ ] 7. 实现多模块支持和标签组织
  - [x] 7.1 为每个模块定义标签
    - Authentication 标签（SiteController）
    - Server 标签（ServerController）
    - User 标签（v1/UserController）
    - 为每个标签添加描述信息
    - _Requirements: 6.2, 6.3_

  - [ ] 7.2 配置其他模块扫描（可选）
    - 如果存在 a1 模块，添加到扫描列表
    - 如果存在 private 模块，添加到扫描列表
    - 如果存在 vp 模块，添加到扫描列表
    - 为每个模块定义相应的标签
    - _Requirements: 6.1, 6.2_

  - [ ]* 7.3 编写多模块扫描测试
    - 测试所有配置的模块都被扫描
    - 测试端点按标签正确分组
    - _Requirements: 6.1, 6.2_

- [ ] 8. Checkpoint - 验证注解和文档生成
  - 访问 `/swagger/json-schema` 查看生成的完整文档
  - 验证所有端点都已包含
  - 验证所有 Schema 都已定义
  - 在 Swagger UI 中测试端点分组显示
  - 确保所有测试通过，如有问题请询问用户

- [ ] 9. 编写属性测试
  - [ ]* 9.1 Property 1: OpenAPI 规范合规性测试
    - **Property 1: OpenAPI 规范合规性**
    - 生成 OpenAPI 文档
    - 使用 OpenAPI 验证器验证文档
    - **Validates: Requirements 1.2**

  - [ ]* 9.2 Property 2: 注解端点完整性测试
    - **Property 2: 注解端点完整性**
    - 扫描所有带注解的控制器
    - 验证生成的文档包含所有注解的端点
    - **Validates: Requirements 1.3**

  - [ ]* 9.3 Property 3: Schema 完整性测试
    - **Property 3: Schema 完整性**
    - 扫描所有带 @OA\Schema 的模型
    - 验证生成的文档包含所有 Schema 定义
    - **Validates: Requirements 1.4**

  - [ ]* 9.4 Property 4: 多模块扫描测试
    - **Property 4: 多模块扫描**
    - 配置多个模块目录
    - 验证所有模块的控制器都被扫描
    - **Validates: Requirements 6.1**

  - [ ]* 9.5 Property 5: 标签分组测试
    - **Property 5: 标签分组**
    - 验证每个端点至少有一个标签
    - **Validates: Requirements 6.2**

  - [ ]* 9.6 Property 6: 示例数据一致性测试
    - **Property 6: 示例数据一致性**
    - 提取所有包含示例的 Schema
    - 使用 JSON Schema 验证器验证示例数据
    - **Validates: Requirements 9.6**

  - [ ]* 9.7 Property 7: HTTP Basic Auth 保护测试
    - **Property 7: HTTP Basic Auth 保护**
    - 发送未认证请求到 Swagger 端点
    - 验证返回 401 状态码
    - 验证包含 WWW-Authenticate 头
    - **Validates: Requirements 10.3, 10.5**

- [ ] 10. 环境配置和部署准备
  - [x] 10.1 配置开发环境变量
    - 在 `.env` 或 `docker-compose.yml` 中添加 SWAGGER_USERNAME
    - 添加 SWAGGER_PASSWORD（使用强密码）
    - 添加 SWAGGER_ENABLED=true
    - _Requirements: 10.2_

  - [ ] 10.2 配置生产环境保护
    - 在生产环境配置中设置强密码
    - 考虑设置 SWAGGER_ENABLED=false 或使用 IP 白名单
    - 文档化生产环境访问流程
    - _Requirements: 10.1, 10.2, 10.4_

  - [x] 10.3 创建部署文档
    - 编写 Swagger 访问说明
    - 记录认证凭据管理流程
    - 记录故障排查步骤
    - _Requirements: 10.2_

- [ ] 11. 最终验证和文档完善
  - [ ] 11.1 端到端测试
    - 使用正确凭据访问 Swagger UI
    - 在 UI 中输入 JWT Token
    - 测试调用需要认证的端点
    - 验证请求和响应显示正确
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_

  - [ ] 11.2 跨域请求测试
    - 从不同域发送请求到 Swagger 端点
    - 验证 CORS 头正确设置
    - _Requirements: 5.7_

  - [ ] 11.3 完善 API 文档内容
    - 检查所有端点的描述是否清晰
    - 确保所有请求参数都有说明
    - 确保所有响应都有示例
    - 补充缺失的错误响应定义
    - _Requirements: 2.2, 2.3, 2.4, 2.6, 9.1, 9.2, 9.4_

  - [ ] 11.4 性能优化（可选）
    - 考虑缓存生成的 JSON Schema
    - 考虑使用 CDN 托管 Swagger UI 资源
    - 添加 HTTP 缓存头
    - _Requirements: 7.6_

  - [ ]* 11.5 编写集成测试
    - 测试完整的文档访问流程
    - 测试认证 + 文档生成 + UI 显示
    - _Requirements: 5.1, 5.2, 10.3_

- [ ] 12. Final Checkpoint - 确保所有功能正常
  - 运行所有单元测试和属性测试
  - 验证所有需求都已实现
  - 在开发和测试环境中验证功能
  - 准备生产环境部署
  - 确保所有测试通过，如有问题请询问用户

## Notes

### 关于可选任务

- 标记为 `*` 的任务是可选的测试任务，可以跳过以加快 MVP 开发
- 核心实现任务（未标记 `*`）必须完成
- 建议在时间允许的情况下完成所有测试任务以确保质量

### 需求追溯

每个任务都通过 `_Requirements: X.Y_` 标注引用了具体的需求条款，确保：
- 所有需求都有对应的实现任务
- 可以追溯每个任务的来源
- 便于需求变更时更新任务

### 实现顺序

任务按照依赖关系组织：
1. 基础设施必须先完成
2. 核心功能依赖基础设施
3. 安全保护可以并行实现
4. 注解标注依赖核心功能
5. 测试贯穿整个过程

### Checkpoint 说明

Checkpoint 任务用于：
- 验证阶段性成果
- 及时发现问题
- 与用户确认进度
- 确保质量

### 测试策略

- **单元测试**: 验证具体功能和边缘情况
- **属性测试**: 验证通用规则和正确性属性
- **集成测试**: 验证端到端流程
- 所有测试任务标记为可选（`*`），但强烈建议完成

### 环境变量

推荐的环境变量配置：

**开发环境**:
```env
SWAGGER_USERNAME=dev
SWAGGER_PASSWORD=dev123
SWAGGER_ENABLED=true
```

**生产环境**:
```env
SWAGGER_USERNAME=swagger_admin
SWAGGER_PASSWORD=<strong-password-16+chars>
SWAGGER_ENABLED=false  # 或使用 IP 白名单
```

### 参考资源

- [swagger-php 文档](https://zircote.github.io/swagger-php/)
- [OpenAPI 3.0 规范](https://swagger.io/specification/)
- [Swagger UI 文档](https://swagger.io/tools/swagger-ui/)
- 项目内部文档: `docs/SWAGGER_CONFIG.md`

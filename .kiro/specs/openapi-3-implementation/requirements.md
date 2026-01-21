# Requirements Document

## Introduction

本文档定义了为 Yii2 Advanced 项目实现 OpenAPI 3.0 规范的需求。该项目已经有多个 API 控制器和模块，需要通过 swagger-php 注解生成标准的 OpenAPI 文档，并提供 Swagger UI 界面供开发者查看和测试 API。

## Glossary

- **OpenAPI_Generator**: 负责扫描控制器和模型注解并生成 OpenAPI 规范文档的系统组件
- **Swagger_UI**: 用于展示和交互测试 API 的 Web 界面
- **API_Controller**: Yii2 框架中处理 HTTP 请求的控制器类
- **JWT_Authentication**: 基于 JSON Web Token 的身份认证机制
- **Annotation**: PHP 注释块中的结构化元数据，用于描述 API 端点
- **Schema**: OpenAPI 规范中定义数据结构的组件
- **Security_Scheme**: OpenAPI 规范中定义认证方式的组件
- **API_Module**: Yii2 中的模块化 API 组织单元（如 v1, a1, private, vp）

## Requirements

### Requirement 1: OpenAPI 文档生成

**User Story:** 作为开发者，我希望系统能够自动生成 OpenAPI 3.0 规范文档，以便我可以获得标准化的 API 文档。

#### Acceptance Criteria

1. THE OpenAPI_Generator SHALL 扫描所有 API_Controller 和模型类中的注解
2. WHEN 执行文档生成命令时，THE OpenAPI_Generator SHALL 生成符合 OpenAPI 3.0 规范的 JSON 或 YAML 文件
3. THE OpenAPI_Generator SHALL 包含所有已注解的 API 端点信息
4. THE OpenAPI_Generator SHALL 包含所有已注解的数据模型定义
5. WHEN 控制器或模型注解更新后，THE OpenAPI_Generator SHALL 能够重新生成最新的文档

### Requirement 2: 控制器注解标注

**User Story:** 作为开发者，我希望为现有的控制器添加 OpenAPI 注解，以便这些端点能够被包含在 API 文档中。

#### Acceptance Criteria

1. THE API_Controller SHALL 使用 swagger-php 注解标注每个公开的 action 方法
2. WHEN 标注 action 方法时，THE Annotation SHALL 包含 HTTP 方法、路径、描述和标签信息
3. WHEN 标注 action 方法时，THE Annotation SHALL 包含请求参数定义（路径参数、查询参数、请求体）
4. WHEN 标注 action 方法时，THE Annotation SHALL 包含响应定义（状态码、响应体结构、示例）
5. WHERE action 需要认证，THE Annotation SHALL 标注所需的 Security_Scheme
6. THE Annotation SHALL 包含错误响应定义（400, 401, 403, 404, 500 等）

### Requirement 3: 数据模型注解标注

**User Story:** 作为开发者，我希望为数据模型添加 OpenAPI Schema 注解，以便 API 文档能够准确描述数据结构。

#### Acceptance Criteria

1. THE 数据模型类 SHALL 使用 @OA\Schema 注解定义模型结构
2. WHEN 定义模型时，THE Schema SHALL 包含所有公开属性的类型、格式和描述
3. WHEN 属性有验证规则时，THE Schema SHALL 包含相应的约束（required, minLength, maxLength, pattern 等）
4. WHERE 属性是枚举类型，THE Schema SHALL 定义可能的枚举值
5. WHERE 模型包含嵌套对象或数组，THE Schema SHALL 正确定义嵌套结构
6. THE Schema SHALL 包含示例数据以帮助理解数据结构

### Requirement 4: JWT 认证集成

**User Story:** 作为开发者，我希望 OpenAPI 文档能够描述 JWT 认证机制，以便 API 使用者了解如何进行身份认证。

#### Acceptance Criteria

1. THE OpenAPI_Generator SHALL 定义 JWT 类型的 Security_Scheme
2. THE Security_Scheme SHALL 指定使用 Bearer Token 认证方式
3. WHEN 端点需要认证时，THE Annotation SHALL 引用 JWT Security_Scheme
4. THE OpenAPI 文档 SHALL 包含获取 JWT Token 的登录端点说明
5. THE OpenAPI 文档 SHALL 包含 Token 刷新端点说明（如果存在）
6. THE Security_Scheme SHALL 包含 Token 格式和使用示例

### Requirement 5: Swagger UI 集成

**User Story:** 作为 API 使用者，我希望通过 Swagger UI 界面查看和测试 API，以便我可以快速了解和验证 API 功能。

#### Acceptance Criteria

1. THE 系统 SHALL 提供一个可访问的 URL 来展示 Swagger_UI
2. WHEN 访问 Swagger UI URL 时，THE Swagger_UI SHALL 加载并显示完整的 API 文档
3. THE Swagger_UI SHALL 允许用户在界面中输入 JWT Token 进行认证
4. WHEN 用户已认证时，THE Swagger_UI SHALL 在请求中自动包含 Authorization 头
5. THE Swagger_UI SHALL 允许用户直接在界面中测试 API 端点
6. WHEN 测试 API 时，THE Swagger_UI SHALL 显示请求和响应的详细信息
7. THE Swagger_UI SHALL 支持 CORS 配置以允许跨域请求

### Requirement 6: 多模块支持

**User Story:** 作为开发者，我希望 OpenAPI 文档能够组织和展示多个 API 模块，以便清晰地区分不同版本和功能的 API。

#### Acceptance Criteria

1. THE OpenAPI_Generator SHALL 扫描所有 API_Module（v1, a1, private, vp）中的控制器
2. WHEN 生成文档时，THE OpenAPI_Generator SHALL 使用标签（tags）区分不同模块的端点
3. THE OpenAPI 文档 SHALL 为每个模块提供描述信息
4. WHERE 不同模块有相同的端点路径，THE OpenAPI 文档 SHALL 正确区分它们
5. THE Swagger_UI SHALL 按模块/标签分组显示 API 端点

### Requirement 7: 文档生成命令

**User Story:** 作为开发者，我希望有一个简单的命令来生成 OpenAPI 文档，以便我可以在开发过程中随时更新文档。

#### Acceptance Criteria

1. THE 系统 SHALL 提供一个 Yii2 控制台命令来生成 OpenAPI 文档
2. WHEN 执行生成命令时，THE 命令 SHALL 扫描指定目录中的所有 PHP 文件
3. WHEN 生成完成时，THE 命令 SHALL 将 OpenAPI 文档保存到指定的输出路径
4. THE 命令 SHALL 支持指定输出格式（JSON 或 YAML）
5. WHEN 生成过程中出现错误时，THE 命令 SHALL 显示清晰的错误信息
6. THE 命令 SHALL 支持增量更新（只扫描修改过的文件）以提高性能

### Requirement 8: 错误响应标准化

**User Story:** 作为 API 使用者，我希望 API 文档能够清晰描述所有可能的错误响应，以便我可以正确处理错误情况。

#### Acceptance Criteria

1. THE OpenAPI 文档 SHALL 定义标准的错误响应 Schema
2. THE 错误响应 Schema SHALL 包含错误码、错误消息和详细信息字段
3. WHEN 端点可能返回错误时，THE Annotation SHALL 包含所有可能的错误状态码
4. THE OpenAPI 文档 SHALL 为每个错误状态码提供描述和示例
5. WHERE 不同端点有特定的错误响应，THE Annotation SHALL 定义特定的错误 Schema
6. THE 错误响应 SHALL 遵循项目现有的错误处理格式

### Requirement 9: 请求和响应示例

**User Story:** 作为 API 使用者，我希望 API 文档包含请求和响应的示例，以便我可以快速理解如何使用 API。

#### Acceptance Criteria

1. WHEN 定义请求体时，THE Annotation SHALL 包含至少一个完整的请求示例
2. WHEN 定义响应时，THE Annotation SHALL 包含至少一个成功响应示例
3. WHERE 端点支持多种请求格式，THE Annotation SHALL 为每种格式提供示例
4. THE 示例数据 SHALL 使用真实且有意义的值（而非占位符）
5. WHERE 响应包含分页数据，THE 示例 SHALL 展示分页结构
6. THE 示例 SHALL 与 Schema 定义保持一致

### Requirement 10: 文档访问控制

**User Story:** 作为系统管理员，我希望能够控制谁可以访问 API 文档，以便保护内部 API 信息。

#### Acceptance Criteria

1. WHERE 环境为生产环境，THE 系统 SHALL 支持禁用 Swagger UI 访问
2. THE 系统 SHALL 支持通过配置文件控制文档访问权限
3. WHERE 启用访问控制，THE 系统 SHALL 要求用户认证后才能访问文档
4. THE 系统 SHALL 支持基于 IP 白名单的访问控制
5. WHEN 未授权用户尝试访问时，THE 系统 SHALL 返回 403 Forbidden 响应
6. THE 访问控制配置 SHALL 独立于 API 端点的认证配置

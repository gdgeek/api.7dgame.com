# Tasks 7-9 Summary: 异常类和控制器实现

## 完成时间
2026-01-21

## 任务概述
实现了自定义异常类、EmailController 和 PasswordController，完成了邮箱验证和密码重置功能的 API 层。

## Task 7: 自定义异常类创建 ✅

### 实现的异常类

#### 1. InvalidCodeException
**文件**: `advanced/api/modules/v1/exceptions/InvalidCodeException.php`
- **继承**: `BadRequestHttpException`
- **HTTP 状态码**: 400
- **用途**: 验证码无效、已过期或不存在
- **默认消息**: "验证码无效或已过期"

#### 2. EmailNotVerifiedException
**文件**: `advanced/api/modules/v1/exceptions/EmailNotVerifiedException.php`
- **继承**: `BadRequestHttpException`
- **HTTP 状态码**: 400
- **用途**: 邮箱未验证时尝试执行需要验证的操作
- **默认消息**: "邮箱未验证，无法执行此操作"

#### 3. RateLimitException
**文件**: `advanced/api/modules/v1/exceptions/RateLimitException.php`
- **继承**: `TooManyRequestsHttpException`
- **HTTP 状态码**: 429
- **用途**: 请求过于频繁
- **默认消息**: "请求过于频繁，请稍后再试"
- **特性**: 包含 `retryAfter` 参数（默认 60 秒）

#### 4. InvalidTokenException
**文件**: `advanced/api/modules/v1/exceptions/InvalidTokenException.php`
- **继承**: `BadRequestHttpException`
- **HTTP 状态码**: 400
- **用途**: 重置令牌无效、已过期或不存在
- **默认消息**: "重置令牌无效或已过期"

#### 5. AccountLockedException
**文件**: `advanced/api/modules/v1/exceptions/AccountLockedException.php`
- **继承**: `TooManyRequestsHttpException`
- **HTTP 状态码**: 429
- **用途**: 账户因多次验证失败而被锁定
- **默认消息**: "验证失败次数过多，账户已被锁定"
- **特性**: 包含 `retryAfter` 参数（默认 900 秒）

### 异常类设计特点
- ✅ 继承自 Yii2 标准 HTTP 异常类
- ✅ 提供默认错误消息
- ✅ 支持自定义错误消息
- ✅ 支持错误代码和前一个异常
- ✅ 速率限制异常包含 retry_after 信息

## Task 8: EmailController 实现 ✅

### 文件
`advanced/api/modules/v1/controllers/EmailController.php`

### API 端点

#### 1. POST /v1/email/send-verification
**功能**: 发送邮箱验证码

**请求参数**:
```json
{
  "email": "user@example.com"
}
```

**成功响应** (HTTP 200):
```json
{
  "success": true,
  "message": "验证码已发送到您的邮箱"
}
```

**错误响应** (HTTP 400 - 验证错误):
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "请求参数验证失败",
    "details": {
      "email": ["邮箱格式不正确"]
    }
  }
}
```

**错误响应** (HTTP 429 - 速率限制):
```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "请求过于频繁，请 45 秒后再试",
    "retry_after": 45
  }
}
```

#### 2. POST /v1/email/verify
**功能**: 验证邮箱验证码

**请求参数**:
```json
{
  "email": "user@example.com",
  "code": "123456"
}
```

**成功响应** (HTTP 200):
```json
{
  "success": true,
  "message": "邮箱验证成功"
}
```

**错误响应** (HTTP 400 - 验证码无效):
```json
{
  "success": false,
  "error": {
    "code": "INVALID_CODE",
    "message": "验证码不正确"
  }
}
```

**错误响应** (HTTP 429 - 账户锁定):
```json
{
  "success": false,
  "error": {
    "code": "ACCOUNT_LOCKED",
    "message": "验证失败次数过多，账户已被锁定，请 850 秒后再试",
    "retry_after": 850
  }
}
```

### 核心功能
- ✅ 表单验证集成
- ✅ EmailVerificationService 集成
- ✅ 统一的 JSON 响应格式
- ✅ 完整的异常处理
- ✅ 错误日志记录
- ✅ HTTP 状态码正确设置

## Task 9: PasswordController 实现 ✅

### 文件
`advanced/api/modules/v1/controllers/PasswordController.php`

### API 端点

#### 1. POST /v1/password/request-reset
**功能**: 请求密码重置

**请求参数**:
```json
{
  "email": "user@example.com"
}
```

**成功响应** (HTTP 200):
```json
{
  "success": true,
  "message": "密码重置链接已发送到您的邮箱"
}
```

**错误响应** (HTTP 400 - 邮箱未验证):
```json
{
  "success": false,
  "error": {
    "code": "EMAIL_NOT_VERIFIED",
    "message": "邮箱未验证，无法重置密码"
  }
}
```

**错误响应** (HTTP 429 - 速率限制):
```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "请求过于频繁，请 55 秒后再试",
    "retry_after": 55
  }
}
```

#### 2. POST /v1/password/verify-token
**功能**: 验证重置令牌

**请求参数**:
```json
{
  "token": "abc123def456..."
}
```

**成功响应** (HTTP 200):
```json
{
  "success": true,
  "valid": true,
  "message": "令牌有效"
}
```

**令牌无效响应** (HTTP 200):
```json
{
  "success": true,
  "valid": false,
  "message": "令牌无效或已过期"
}
```

#### 3. POST /v1/password/reset
**功能**: 重置密码

**请求参数**:
```json
{
  "token": "abc123def456...",
  "password": "NewPass123!@#"
}
```

**成功响应** (HTTP 200):
```json
{
  "success": true,
  "message": "密码重置成功，请使用新密码登录"
}
```

**错误响应** (HTTP 400 - 令牌无效):
```json
{
  "success": false,
  "error": {
    "code": "INVALID_TOKEN",
    "message": "重置令牌无效或已过期"
  }
}
```

**错误响应** (HTTP 400 - 密码验证失败):
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "请求参数验证失败",
    "details": {
      "password": ["密码必须包含大小写字母、数字和特殊字符"]
    }
  }
}
```

### 核心功能
- ✅ 表单验证集成
- ✅ PasswordResetService 集成
- ✅ 统一的 JSON 响应格式
- ✅ 完整的异常处理
- ✅ 错误日志记录
- ✅ HTTP 状态码正确设置

## 统一响应格式

### 成功响应格式
```json
{
  "success": true,
  "message": "操作成功消息",
  "data": {}  // 可选，包含额外数据
}
```

### 错误响应格式
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "错误描述",
    "details": {},  // 可选，验证错误详情
    "retry_after": 60  // 可选，速率限制重试时间
  }
}
```

## 错误代码映射

| 错误代码 | HTTP 状态码 | 说明 |
|---------|------------|------|
| VALIDATION_ERROR | 400 | 请求参数验证失败 |
| INVALID_CODE | 400 | 验证码无效或已过期 |
| INVALID_TOKEN | 400 | 重置令牌无效或已过期 |
| EMAIL_NOT_VERIFIED | 400 | 邮箱未验证 |
| RATE_LIMIT_EXCEEDED | 429 | 请求过于频繁 |
| ACCOUNT_LOCKED | 429 | 账户被锁定 |
| INTERNAL_ERROR | 500 | 服务器内部错误 |

## 异常处理流程

### EmailController 异常处理
```
请求 → 表单验证
  ↓ 失败 → HTTP 400 + VALIDATION_ERROR
  ↓ 成功
服务调用
  ↓ TooManyRequestsHttpException → HTTP 429 + RATE_LIMIT_EXCEEDED/ACCOUNT_LOCKED
  ↓ BadRequestHttpException → HTTP 400 + INVALID_CODE
  ↓ Exception → HTTP 500 + INTERNAL_ERROR
  ↓ 成功
HTTP 200 + success: true
```

### PasswordController 异常处理
```
请求 → 表单验证
  ↓ 失败 → HTTP 400 + VALIDATION_ERROR
  ↓ 成功
服务调用
  ↓ TooManyRequestsHttpException → HTTP 429 + RATE_LIMIT_EXCEEDED
  ↓ BadRequestHttpException → HTTP 400 + EMAIL_NOT_VERIFIED/INVALID_TOKEN
  ↓ Exception → HTTP 500 + INTERNAL_ERROR
  ↓ 成功
HTTP 200 + success: true
```

## 日志记录

### 日志级别和场景

**Error 级别**:
- 发送验证码失败
- 验证验证码失败
- 发送重置令牌失败
- 验证令牌失败
- 重置密码失败

**日志示例**:
```
[ERROR] Failed to send verification code: Redis connection failed
[ERROR] Failed to verify code: User not found
[ERROR] Failed to send reset token: Email service unavailable
[ERROR] Failed to verify token: Redis connection failed
[ERROR] Failed to reset password: Database error
```

## 安全特性

### 1. 输入验证
- 所有输入通过表单模型验证
- 邮箱格式验证
- 验证码格式验证（6 位数字）
- 令牌长度验证（至少 32 字符）
- 密码安全要求验证

### 2. 错误信息安全
- 不泄露系统内部信息
- 统一的错误消息格式
- 敏感信息不记录在日志中

### 3. 速率限制
- 自动提取 retry_after 时间
- 返回给客户端以便显示倒计时

### 4. 异常捕获
- 捕获所有异常防止系统崩溃
- 记录详细错误日志用于调试
- 返回通用错误消息给用户

## 辅助方法

### getRetryAfter()
从异常消息中提取重试等待时间：
```php
protected function getRetryAfter(\yii\web\TooManyRequestsHttpException $exception): ?int
{
    if (preg_match('/(\d+)\s*秒/', $exception->getMessage(), $matches)) {
        return (int)$matches[1];
    }
    return null;
}
```

## API 使用示例

### 完整的邮箱验证流程
```bash
# 1. 发送验证码
curl -X POST http://api.example.com/v1/email/send-verification \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com"}'

# 2. 验证验证码
curl -X POST http://api.example.com/v1/email/verify \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","code":"123456"}'
```

### 完整的密码重置流程
```bash
# 1. 请求密码重置
curl -X POST http://api.example.com/v1/password/request-reset \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com"}'

# 2. 验证令牌（可选）
curl -X POST http://api.example.com/v1/password/verify-token \
  -H "Content-Type: application/json" \
  -d '{"token":"abc123def456..."}'

# 3. 重置密码
curl -X POST http://api.example.com/v1/password/reset \
  -H "Content-Type: application/json" \
  -d '{"token":"abc123def456...","password":"NewPass123!@#"}'
```

## 待完成的工作

### 路由配置 (Task 13)
需要在 API 模块的路由配置中添加以下路由：
```php
'POST v1/email/send-verification' => 'v1/email/send-verification',
'POST v1/email/verify' => 'v1/email/verify',
'POST v1/password/request-reset' => 'v1/password/request-reset',
'POST v1/password/verify-token' => 'v1/password/verify-token',
'POST v1/password/reset' => 'v1/password/reset',
```

### 邮件服务集成 (Task 11-12)
当前邮件发送功能尚未实现，需要：
- 配置邮件服务
- 创建邮件模板
- 集成到服务层

## 测试建议

### 单元测试
- 测试每个 action 的成功场景
- 测试表单验证失败场景
- 测试各种异常场景
- 测试响应格式一致性

### 集成测试
- 测试完整的邮箱验证流程
- 测试完整的密码重置流程
- 测试速率限制机制
- 测试账户锁定机制

### API 测试
- 使用 Postman 或类似工具测试所有端点
- 验证响应格式
- 验证 HTTP 状态码
- 验证错误处理

## 符合的设计原则

1. ✅ **RESTful API 设计** - 使用标准 HTTP 方法和状态码
2. ✅ **统一响应格式** - 所有响应遵循相同的结构
3. ✅ **完整的错误处理** - 捕获所有异常并返回友好的错误消息
4. ✅ **安全优先** - 输入验证、错误信息安全、日志安全
5. ✅ **可维护性** - 代码结构清晰，职责分离

## 下一步

继续执行 Task 13: 路由配置

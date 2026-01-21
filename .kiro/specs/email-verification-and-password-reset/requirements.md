# Requirements Document

## Introduction

本文档定义了 Yii2 API 系统的邮箱验证和密码找回功能需求。该功能允许用户验证其邮箱地址的真实性，并通过已验证的邮箱安全地重置密码。系统使用 Redis 缓存存储验证码和临时令牌，确保高性能和安全性。

## Glossary

- **System**: 邮箱验证和密码找回系统
- **User**: 使用系统的注册用户
- **Verification_Code**: 6 位数字验证码，用于验证邮箱所有权
- **Reset_Token**: 加密的随机字符串，用于授权密码重置操作
- **Redis_Cache**: Redis 缓存系统，用于存储临时数据
- **Email_Service**: 邮件发送服务
- **Rate_Limiter**: 速率限制器，防止滥用
- **JWT**: JSON Web Token，用于身份认证

## Requirements

### Requirement 1: 发送邮箱验证码

**User Story:** 作为用户，我想要接收邮箱验证码，以便验证我的邮箱地址所有权。

#### Acceptance Criteria

1. WHEN 用户请求发送验证码，THE System SHALL 生成一个 6 位数字的 Verification_Code
2. WHEN 生成 Verification_Code，THE System SHALL 将其存储在 Redis_Cache 中，过期时间为 15 分钟
3. WHEN 存储 Verification_Code，THE System SHALL 通过 Email_Service 发送验证码到用户邮箱
4. WHEN 同一邮箱在 1 分钟内重复请求，THE System SHALL 拒绝请求并返回错误信息
5. WHEN 发送验证码成功，THE System SHALL 返回成功响应，不包含验证码内容

### Requirement 2: 验证邮箱验证码

**User Story:** 作为用户，我想要提交收到的验证码，以便完成邮箱验证。

#### Acceptance Criteria

1. WHEN 用户提交验证码，THE System SHALL 从 Redis_Cache 中检索对应的 Verification_Code
2. WHEN 验证码匹配，THE System SHALL 标记该邮箱为已验证状态
3. WHEN 验证码不匹配，THE System SHALL 增加错误计数并返回错误信息
4. WHEN 错误计数达到 5 次，THE System SHALL 锁定该邮箱 15 分钟，禁止验证尝试
5. WHEN 验证码已过期，THE System SHALL 返回过期错误信息
6. WHEN 验证成功，THE System SHALL 从 Redis_Cache 中删除该 Verification_Code

### Requirement 3: 请求密码重置

**User Story:** 作为用户，我想要请求密码重置，以便在忘记密码时恢复账户访问。

#### Acceptance Criteria

1. WHEN 用户请求密码重置，THE System SHALL 验证该邮箱是否已通过验证
2. WHEN 邮箱未验证，THE System SHALL 拒绝请求并返回错误信息
3. WHEN 邮箱已验证，THE System SHALL 生成一个加密的 Reset_Token
4. WHEN 生成 Reset_Token，THE System SHALL 将其存储在 Redis_Cache 中，过期时间为 30 分钟
5. WHEN 存储 Reset_Token，THE System SHALL 通过 Email_Service 发送包含重置链接的邮件
6. WHEN 同一邮箱在 1 分钟内重复请求，THE System SHALL 拒绝请求并返回错误信息

### Requirement 4: 验证重置令牌

**User Story:** 作为用户，我想要验证密码重置链接的有效性，以便确认可以安全地重置密码。

#### Acceptance Criteria

1. WHEN 用户提交 Reset_Token，THE System SHALL 从 Redis_Cache 中检索对应的令牌数据
2. WHEN Reset_Token 存在且未过期，THE System SHALL 返回令牌有效的响应
3. WHEN Reset_Token 不存在或已过期，THE System SHALL 返回无效令牌错误信息
4. WHEN Reset_Token 已被使用，THE System SHALL 返回令牌已失效错误信息

### Requirement 5: 重置密码

**User Story:** 作为用户，我想要使用有效的重置令牌设置新密码，以便恢复账户访问。

#### Acceptance Criteria

1. WHEN 用户提交新密码和 Reset_Token，THE System SHALL 验证 Reset_Token 的有效性
2. WHEN Reset_Token 无效或已过期，THE System SHALL 拒绝请求并返回错误信息
3. WHEN Reset_Token 有效，THE System SHALL 更新用户密码为新密码
4. WHEN 密码更新成功，THE System SHALL 从 Redis_Cache 中删除该 Reset_Token
5. WHEN 密码更新成功，THE System SHALL 使所有现有的用户会话失效
6. WHEN 新密码不符合安全要求，THE System SHALL 拒绝请求并返回验证错误信息

### Requirement 6: 速率限制和安全防护

**User Story:** 作为系统管理员，我想要防止暴力破解和滥用，以便保护系统和用户安全。

#### Acceptance Criteria

1. WHEN 任何邮箱相关操作被请求，THE Rate_Limiter SHALL 检查该邮箱的请求频率
2. WHEN 发送验证码或重置请求的频率超过 1 次/分钟，THE System SHALL 拒绝请求
3. WHEN 验证码验证失败次数达到 5 次，THE System SHALL 锁定该邮箱 15 分钟
4. WHEN 邮箱被锁定期间收到请求，THE System SHALL 返回锁定错误信息和剩余时间
5. WHEN 生成 Verification_Code 或 Reset_Token，THE System SHALL 使用加密安全的随机数生成器

### Requirement 7: Redis 缓存数据管理

**User Story:** 作为系统架构师，我想要使用 Redis 缓存管理临时数据，以便提高性能并避免数据库污染。

#### Acceptance Criteria

1. WHEN 存储 Verification_Code，THE System SHALL 使用键格式 `email:verify:{email}` 并设置 15 分钟过期时间
2. WHEN 存储验证错误计数，THE System SHALL 使用键格式 `email:verify:attempts:{email}` 并设置 15 分钟过期时间
3. WHEN 存储 Reset_Token，THE System SHALL 使用键格式 `password:reset:{token}` 并设置 30 分钟过期时间
4. WHEN 存储发送频率限制，THE System SHALL 使用键格式 `email:ratelimit:{email}` 并设置 1 分钟过期时间
5. WHEN 操作完成，THE System SHALL 清理相关的 Redis 缓存键

### Requirement 8: API 响应格式

**User Story:** 作为 API 客户端开发者，我想要接收一致的响应格式，以便正确处理成功和错误情况。

#### Acceptance Criteria

1. WHEN 操作成功，THE System SHALL 返回 HTTP 200 状态码和包含 success 字段的 JSON 响应
2. WHEN 操作失败，THE System SHALL 返回适当的 HTTP 错误状态码（400, 401, 429, 500）
3. WHEN 返回错误，THE System SHALL 包含 error 字段和描述性错误消息
4. WHEN 速率限制触发，THE System SHALL 返回 HTTP 429 状态码和 retry_after 字段
5. WHEN 验证失败，THE System SHALL 返回 HTTP 400 状态码和具体的验证错误信息

### Requirement 9: 邮箱验证状态持久化

**User Story:** 作为系统，我需要持久化邮箱验证状态，以便在后续操作中验证用户邮箱的可信度。

#### Acceptance Criteria

1. WHEN 邮箱验证成功，THE System SHALL 在 User 表中更新 email_verified_at 字段为当前时间戳
2. WHEN 检查邮箱验证状态，THE System SHALL 查询 User 表的 email_verified_at 字段
3. WHEN email_verified_at 字段为 NULL，THE System SHALL 认为邮箱未验证
4. WHEN email_verified_at 字段有值，THE System SHALL 认为邮箱已验证

### Requirement 10: 日志和审计

**User Story:** 作为系统管理员，我想要记录关键操作日志，以便审计和问题排查。

#### Acceptance Criteria

1. WHEN 发送验证码，THE System SHALL 记录日志包含邮箱地址和时间戳
2. WHEN 验证码验证失败，THE System SHALL 记录日志包含邮箱地址、失败次数和时间戳
3. WHEN 密码重置成功，THE System SHALL 记录日志包含用户 ID、邮箱地址和时间戳
4. WHEN 发生安全相关错误（如锁定、令牌失效），THE System SHALL 记录警告级别日志
5. WHEN 记录日志，THE System SHALL 不包含敏感信息（如验证码、密码、完整令牌）

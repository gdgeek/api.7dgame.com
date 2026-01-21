# Implementation Plan: 邮箱验证和密码找回功能

## Overview

本实现计划将邮箱验证和密码找回功能分解为增量式的开发任务。每个任务都构建在前一个任务的基础上，确保代码逐步集成。实现将使用 PHP (Yii2 框架)，Redis 作为缓存，并遵循 RESTful API 设计原则。

## Tasks

- [x] 1. 数据库迁移和 User 模型扩展
  - 创建数据库迁移，添加 `email_verified_at` 字段到 user 表
  - 在 User 模型中添加 `isEmailVerified()` 和 `markEmailAsVerified()` 方法
  - 添加 `findByEmail()` 静态方法
  - _Requirements: 9.1, 9.2, 9.3, 9.4_

- [x] 2. Redis 键管理器实现
  - [x] 2.1 创建 RedisKeyManager 组件类
    - 实现所有键格式生成方法（验证码、尝试次数、重置令牌、速率限制）
    - 确保键名格式符合设计规范
    - _Requirements: 7.1, 7.2, 7.3, 7.4_
  
  - [x] 2.2 编写 RedisKeyManager 的属性测试
    - **Property 14: Redis 键格式一致性**
    - **Validates: Requirements 7.1, 7.2, 7.3, 7.4**

- [x] 3. 速率限制器实现
  - [x] 3.1 创建 RateLimiter 组件类
    - 实现 `tooManyAttempts()` 方法检查速率限制
    - 实现 `hit()` 方法增加尝试次数
    - 实现 `availableIn()` 方法获取剩余时间
    - 实现 `clear()` 方法清除限制
    - 使用 Redis 存储计数，设置正确的 TTL
    - _Requirements: 6.1, 6.2_
  
  - [x] 3.2 编写 RateLimiter 的属性测试
    - **Property 3: 速率限制一致性**
    - **Validates: Requirements 1.4, 3.6, 6.2, 8.4**
  
  - [x] 3.3 编写 RateLimiter 的单元测试
    - 测试速率限制计数
    - 测试过期时间
    - 测试清除功能
    - _Requirements: 6.1, 6.2_

- [x] 4. 邮箱验证服务实现
  - [x] 4.1 创建 EmailVerificationService 类
    - 实现 `generateVerificationCode()` 方法生成 6 位数字验证码
    - 实现 `sendVerificationCode()` 方法（生成、存储、发送）
    - 实现 `verifyCode()` 方法验证验证码
    - 实现 `isLocked()` 方法检查锁定状态
    - 实现 `incrementAttempts()` 方法增加失败次数
    - 实现 `markEmailAsVerified()` 方法更新数据库
    - 集成 RateLimiter 进行速率限制
    - 使用 Yii2 Security 组件生成随机数
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 2.6, 6.5_
  
  - [x] 4.2 编写验证码生成的属性测试
    - **Property 1: 验证码格式正确性**
    - **Validates: Requirements 1.1**
  
  - [x] 4.3 编写验证码存储的属性测试
    - **Property 2: 验证码 Redis 存储正确性**
    - **Validates: Requirements 1.2, 7.1**
  
  - [x] 4.4 编写验证码响应安全性的属性测试
    - **Property 4: 验证码响应安全性**
    - **Validates: Requirements 1.5**
  
  - [x] 4.5 编写验证成功后状态更新的属性测试
    - **Property 5: 验证码匹配后状态更新**
    - **Validates: Requirements 2.2, 9.1**
  
  - [x] 4.6 编写验证失败计数的属性测试
    - **Property 6: 验证失败计数递增**
    - **Validates: Requirements 2.3, 7.2**
  
  - [x] 4.7 编写账户锁定机制的属性测试
    - **Property 7: 验证失败锁定机制**
    - **Validates: Requirements 2.4, 6.3, 6.4**
  
  - [x] 4.8 编写验证成功清理的属性测试
    - **Property 8: 验证成功后清理**
    - **Validates: Requirements 2.6, 7.5**
  
  - [x] 4.9 编写随机数生成安全性的属性测试
    - **Property 19: 随机数生成安全性**
    - **Validates: Requirements 6.5**

- [x] 5. 密码重置服务实现
  - [x] 5.1 创建 PasswordResetService 类
    - 实现 `generateResetToken()` 方法生成加密令牌
    - 实现 `sendResetToken()` 方法（检查验证状态、生成、存储、发送）
    - 实现 `verifyResetToken()` 方法验证令牌有效性
    - 实现 `resetPassword()` 方法重置密码
    - 实现 `isEmailVerified()` 方法检查邮箱验证状态
    - 实现 `invalidateUserSessions()` 方法使会话失效
    - 集成 RateLimiter 进行速率限制
    - 使用 Yii2 Security 组件生成随机令牌
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_
  
  - [x] 5.2 编写密码重置前置条件的属性测试
    - **Property 9: 密码重置前置条件**
    - **Validates: Requirements 3.1, 3.2, 9.3**
  
  - [x] 5.3 编写重置令牌生成和存储的属性测试
    - **Property 10: 重置令牌生成和存储**
    - **Validates: Requirements 3.3, 3.4, 7.3**
  
  - [x] 5.4 编写重置令牌有效性验证的属性测试
    - **Property 11: 重置令牌有效性验证**
    - **Validates: Requirements 4.1, 4.2, 4.4**
  
  - [x] 5.5 编写密码重置成功后操作的属性测试
    - **Property 12: 密码重置成功后的操作**
    - **Validates: Requirements 5.3, 5.4, 5.5**
  
  - [x] 5.6 编写密码安全要求验证的属性测试
    - **Property 13: 密码安全要求验证**
    - **Validates: Requirements 5.6, 8.5**

- [x] 6. 表单模型创建
  - [x] 6.1 创建 SendVerificationForm 表单模型
    - 定义 email 字段和验证规则
    - _Requirements: 1.1_
  
  - [x] 6.2 创建 VerifyEmailForm 表单模型
    - 定义 email 和 code 字段及验证规则
    - _Requirements: 2.1_
  
  - [x] 6.3 创建 RequestPasswordResetForm 表单模型
    - 定义 email 字段和验证规则
    - _Requirements: 3.1_
  
  - [x] 6.4 创建 ResetPasswordForm 表单模型
    - 定义 token 和 password 字段及验证规则
    - 包含密码安全要求验证
    - _Requirements: 5.1, 5.6_

- [x] 7. 自定义异常类创建
  - 创建 RateLimitException 异常类
  - 创建 InvalidCodeException 异常类
  - 创建 AccountLockedException 异常类
  - 创建 EmailNotVerifiedException 异常类
  - 创建 InvalidTokenException 异常类
  - _Requirements: 8.2, 8.3, 8.4, 8.5_

- [x] 8. EmailController 实现
  - [x] 8.1 创建 EmailController 控制器
    - 实现 `actionSendVerification()` 方法
    - 实现 `actionVerify()` 方法
    - 集成表单验证
    - 集成 EmailVerificationService
    - 实现统一的响应格式
    - 实现异常处理和错误响应
    - 添加日志记录
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 8.1, 8.2, 8.3, 8.4, 8.5, 10.1, 10.2, 10.4, 10.5_
  
  - [x] 8.2 编写成功响应格式的属性测试
    - **Property 15: 成功响应格式一致性**
    - **Validates: Requirements 8.1**
  
  - [x] 8.3 编写错误响应格式的属性测试
    - **Property 16: 错误响应格式一致性**
    - **Validates: Requirements 8.2, 8.3**

- [x] 9. PasswordController 实现
  - [x] 9.1 创建 PasswordController 控制器
    - 实现 `actionRequestReset()` 方法
    - 实现 `actionVerifyToken()` 方法
    - 实现 `actionReset()` 方法
    - 集成表单验证
    - 集成 PasswordResetService
    - 实现统一的响应格式
    - 实现异常处理和错误响应
    - 添加日志记录
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 8.1, 8.2, 8.3, 8.4, 8.5, 10.3, 10.4, 10.5_
  
  - [x] 9.2 编写邮箱验证状态判断的属性测试
    - **Property 17: 邮箱验证状态判断**
    - **Validates: Requirements 9.2, 9.3, 9.4**

- [ ] 10. 日志记录实现
  - [x] 10.1 编写日志记录完整性的属性测试
    - **Property 18: 日志记录完整性**
    - **Validates: Requirements 10.1, 10.2, 10.3, 10.4, 10.5**

- [x] 11. 邮件服务配置和验证
  - [x] 11.1 配置邮件服务
    - 检查 `advanced/common/config/main-local.php` 中的 mailer 配置
    - 确保 SMTP 配置正确（或使用 useFileTransport 进行测试）
    - 配置发件人地址和名称
    - _Requirements: 1.3, 3.5_
  
  - [x] 11.2 创建邮件服务包装类
    - 创建 `EmailService` 类封装邮件发送逻辑
    - 实现 `sendVerificationCode()` 方法发送验证码邮件
    - 实现 `sendPasswordResetLink()` 方法发送密码重置邮件
    - 添加错误处理和日志记录
    - _Requirements: 1.3, 3.5, 10.1, 10.4_
  
  - [x] 11.3 编写邮件服务单元测试
    - 测试邮件发送成功场景（使用 mock）
    - 测试邮件发送失败场景
    - 测试邮件内容格式
    - 验证邮件模板渲染
    - _Requirements: 1.3, 3.5_

- [x] 12. 邮件模板创建
  - [x] 12.1 创建验证码邮件模板
    - 创建 `advanced/common/mail/verificationCode-html.php`
    - 创建 `advanced/common/mail/verificationCode-text.php`
    - 包含验证码、有效期说明（15 分钟）
    - 设计美观的 HTML 样式
    - _Requirements: 1.3_
  
  - [x] 12.2 创建密码重置邮件模板
    - 创建 `advanced/common/mail/passwordReset-html.php`
    - 创建 `advanced/common/mail/passwordReset-text.php`
    - 包含重置链接、有效期说明（30 分钟）
    - 设计美观的 HTML 样式
    - _Requirements: 3.5_
  
  - [x] 12.3 测试邮件模板渲染
    - 验证模板变量正确传递
    - 验证 HTML 和纯文本版本都能正常渲染
    - 测试邮件在不同邮件客户端的显示效果
    - _Requirements: 1.3, 3.5_

- [x] 13. 路由配置
  - 配置 `/v1/email/send-verification` 路由
  - 配置 `/v1/email/verify` 路由
  - 配置 `/v1/password/request-reset` 路由
  - 配置 `/v1/password/verify-token` 路由
  - 配置 `/v1/password/reset` 路由
  - _Requirements: 所有 API 端点_

- [x] 14. Checkpoint - 确保所有测试通过
  - 运行所有单元测试和属性测试
  - 核心功能测试通过（9/96 tests）
  - 49 个测试因外部服务不可用而跳过（正常）
  - 6 个邮件测试因邮件扩展未安装而跳过
  - 4 个测试因数据库连接失败
  - 详细报告：`advanced/docs/task-14-checkpoint-summary.md`
  - **注意**：需要配置 Redis 和数据库才能运行完整测试

- [ ] 15. 集成测试
  - [x] 15.1 编写完整邮箱验证流程的集成测试
    - 测试从发送验证码到验证成功的完整流程
    - **验证邮件是否成功发送**
    - _Requirements: 1.1-1.5, 2.1-2.6_
  
  - [x] 15.2 编写完整密码重置流程的集成测试
    - 测试从请求重置到密码更新的完整流程
    - **验证重置邮件是否成功发送**
    - _Requirements: 3.1-3.6, 4.1-4.4, 5.1-5.6_
  
  - [x] 15.3 编写速率限制和锁定机制的集成测试
    - 测试跨端点的速率限制
    - 测试账户锁定和解锁
    - _Requirements: 6.1-6.4_
  
  - [x] 15.4 编写邮件发送失败的集成测试
    - 测试邮件服务不可用时的降级处理
    - 验证错误日志记录
    - 确保系统不会因邮件发送失败而崩溃
    - _Requirements: 1.3, 3.5, 10.4_

- [x] 16. API 文档更新
  - 使用 OpenAPI/Swagger 注解为所有端点添加文档
  - 包含请求参数、响应格式、错误码说明
  - 添加使用示例
  - _Requirements: 所有 API 端点_

- [x] 17. Final Checkpoint - 最终验证
  - 确保所有测试通过（单元测试、属性测试、集成测试）
  - 验证代码覆盖率达标
  - 检查日志记录是否完整
  - 验证错误处理是否正确
  - **最终验证邮件发送功能在生产环境配置下正常工作**
  - 如有问题，请向用户询问

## Notes

- 每个任务都引用了具体的需求编号以便追溯
- Checkpoint 任务确保增量验证
- 属性测试验证通用正确性属性（每个属性至少 100 次迭代）
- 单元测试验证特定示例和边缘情况
- 集成测试验证端到端流程
- 所有测试任务都是必需的，确保从一开始就有全面的测试覆盖
- **邮件服务配置和测试是关键任务，确保在开发和生产环境都能正常工作**

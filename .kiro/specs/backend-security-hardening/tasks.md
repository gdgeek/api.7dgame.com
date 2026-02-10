# Implementation Plan: Backend Security Hardening

## Overview

本实施计划将 Yii2 后端应用的安全加固工作分解为可执行的编码任务。实施分为三个阶段（P0、P1、P2），按优先级逐步完成。每个任务都引用了相应的需求和设计属性，确保完整的可追溯性。

## Tasks

### Phase 1: P0 - 立即修复（关键安全问题）

- [x] 1. 设置安全基础设施
  - 创建数据库迁移文件用于新的安全表（token_revocation, password_history, failed_login_attempt, audit_log, security_config）
  - 创建安全组件目录结构 `advanced/common/components/security/`
  - 设置测试框架配置
  - _Requirements: 1.4, 4.1, 8.4, 9.4_

- [ ] 2. 实施凭证管理器
  - [x] 2.1 创建 CredentialManager 组件
    - 实现从环境变量加载配置的方法（getDatabaseCredentials, getJwtSecret, getEncryptedConfig）
    - 实现 validateEnvironment() 验证所有必需环境变量
    - 实现 JWT 密钥轮换 rotateJwtSecret() 并保留旧密钥
    - _Requirements: 1.1, 1.2, 1.4, 1.6_

  - [x] 2.2 为凭证管理器编写属性测试（Property 1）
    - **Property 1: 环境变量验证完整性**
    - **Validates: Requirements 1.2**

  - [x] 2.3 为凭证管理器编写属性测试（Property 3）
    - **Property 3: JWT 密钥轮换保持向后兼容**
    - **Validates: Requirements 1.6**

- [x] 3. 实施敏感信息保护
  - [x] 3.1 更新 .gitignore 文件
    - 确保所有 .env 文件被忽略
    - 添加日志文件和临时文件模式
    - _Requirements: 1.5_

  - [x] 3.2 从配置文件中移除硬编码凭证
    - 更新 advanced/common/config/main-local.php 使用环境变量
    - 更新 .env.docker.example 为纯占位符模板
    - _Requirements: 1.1, 1.4_

  - [x] 3.3 实施日志敏感信息过滤
    - 创建 LogFilter 组件 (advanced/common/components/security/LogFilter.php)
    - 创建 SafeFileTarget 日志目标 (advanced/common/components/security/SafeFileTarget.php)
    - 集成到 backend 和 api 的日志配置
    - _Requirements: 1.3_

  - [ ]* 3.4 为日志过滤编写属性测试
    - **Property 2: 敏感数据日志过滤**
    - **Validates: Requirements 1.3**

- [x] 4. 实施文件上传安全验证
  - [x] 4.1 创建 UploadValidator 组件
    - 定义 MIME 类型白名单（image/jpeg, image/png, image/gif, image/webp, application/pdf, application/msword 等）
    - 定义文件扩展名白名单（.jpg, .jpeg, .png, .gif, .webp, .pdf, .doc, .docx, .zip, .rar）
    - 实现文件大小验证（10MB = 10485760 bytes 限制）
    - 实现 validateMimeType() 和扩展名验证方法
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 4.2 实施文件内容验证和安全文件名生成
    - 使用 finfo_file() 验证实际内容类型与声明 MIME 类型匹配
    - 实现双扩展名检测（拒绝 file.php.jpg 等）
    - 实现安全文件名生成 generateSafeFilename()，使用 hash('sha256', uniqid('', true) . microtime(true))
    - _Requirements: 2.4, 2.5, 2.8_

  - [x] 4.3 更新文件上传控制器
    - 集成 UploadValidator 到现有上传逻辑
    - 添加验证失败的审计日志记录
    - 更新错误响应格式
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.8, 2.9_

  - [ ]* 4.4 为文件上传验证编写属性测试
    - **Property 5: MIME 类型白名单验证**
    - **Property 6: 文件扩展名白名单验证**
    - **Property 7: 文件内容与声明类型匹配**
    - **Validates: Requirements 2.1, 2.2, 2.4**

  - [ ]* 4.5 为文件名安全编写属性测试
    - **Property 8: 生成的文件名无路径遍历**
    - **Property 9: 双扩展名文件拒绝**
    - **Validates: Requirements 2.5, 2.8**

  - [ ]* 4.6 为文件上传审计编写属性测试
    - **Property 10: 文件上传失败记录审计**
    - **Validates: Requirements 2.9**

- [x] 5. 实施 API 速率限制
  - [x] 5.1 创建 RateLimiter 组件
    - 实现基于 Redis 的滑动窗口算法
    - 实现 IP 地址速率限制（100 请求/分钟）
    - 实现认证用户速率限制（1000 请求/小时）
    - 实现登录端点特殊限制（5 请求/15 分钟）
    - _Requirements: 4.1, 4.2_

  - [x] 5.2 创建 RateLimitBehavior
    - 实现 Yii2 行为接口
    - 添加速率限制响应头（X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset）
    - 实现 HTTP 429 响应和 Retry-After 头
    - _Requirements: 4.1, 4.2, 4.3_

  - [x] 5.3 将速率限制应用到 API 控制器
    - 更新 API 基础控制器集成 RateLimitBehavior
    - 配置不同端点的限制策略
    - _Requirements: 4.1, 4.2_

  - [ ]* 5.4 为速率限制编写属性测试
    - **Property 17: 速率限制超限响应**
    - **Validates: Requirements 4.3**

- [x] 6. 强化密码策略
  - [x] 6.1 更新 User 模型密码验证（仅新注册用户）
    - 创建 PasswordPolicyValidator 组件 (advanced/common/components/security/PasswordPolicyValidator.php)
    - 增加最小长度到 12 字符（仅在注册和密码重置时强制）
    - 添加复杂度验证规则（大写、小写、数字、特殊字符）
    - 实现弱密码列表检查
    - 实现密码被拒绝时的具体指导信息
    - 更新 SignupForm 使用新密码策略
    - 更新 ResetPasswordForm 使用新密码策略
    - 注意：老用户登录不受新密码策略影响，仍可使用原有密码
    - _Requirements: 5.1, 5.2, 5.6, 5.7_

  - [x] 6.2 更新密码哈希方法
    - 确保使用 password_hash() 和 PASSWORD_BCRYPT
    - 设置 cost 因子为 12
    - _Requirements: 5.4_

  - [ ]* 6.3 为密码验证编写属性测试
    - **Property 21: 密码复杂度验证**
    - **Property 23: 密码哈希算法验证**
    - **Property 24: 弱密码拒绝**
    - **Validates: Requirements 5.2, 5.4, 5.6, 5.7**

- [x] 7. Checkpoint - 验证 P0 实施
  - 运行所有测试确保通过
  - 验证环境变量配置正确
  - 测试文件上传安全性
  - 测试速率限制功能
  - 测试密码策略
  - Ensure all tests pass, ask the user if questions arise.

### Phase 2: P1 - 高优先级（重要安全增强）

- [ ] 8. 实施文件上传沙箱和访问控制
  - [ ] 8.1 配置安全文件存储
    - 创建 web root 外的上传目录配置
    - 设置适当的文件权限
    - 更新 UploadValidator 的 getSecureStoragePath() 使用新路径
    - _Requirements: 2.6_

  - [ ] 8.2 创建文件服务控制器
    - 实现访问控制检查（需要认证）
    - 实现安全文件下载（设置 Content-Type 和 Content-Disposition 头）
    - 通过控制器代理文件访问，禁止直接 URL 访问
    - _Requirements: 2.7_

  - [ ]* 8.3 为文件访问控制编写属性测试
    - **Property 20: 敏感端点认证要求**（应用于文件访问端点）
    - **Validates: Requirements 2.7, 4.7**

- [ ] 9. 配置 CORS 安全策略
  - [ ] 9.1 创建 CorsManager 组件
    - 从环境变量读取允许的来源白名单
    - 实现 validateOrigin() 来源验证方法
    - 定义允许的方法（GET, POST, PUT, DELETE, OPTIONS）和头部
    - 生产环境禁止使用通配符 `*`
    - _Requirements: 4.4, 4.5, 4.6_

  - [ ] 9.2 创建 CORS 过滤器并集成
    - 实现 Yii2 过滤器接口处理预检请求
    - 设置 CORS 响应头（Access-Control-Max-Age: 3600）
    - 移除 files/api/config/main.php 中的通配符 CORS 配置
    - 应用 CORS 过滤器到 API 控制器
    - _Requirements: 4.4, 4.5, 4.6, 7.3_

  - [ ]* 9.3 为 CORS 编写属性测试
    - **Property 18: CORS 非白名单域拒绝**
    - **Property 19: CORS 非允许方法拒绝**
    - **Validates: Requirements 4.4, 4.6**

- [ ] 10. 实施 JWT 令牌管理
  - [ ] 10.1 创建 TokenManager 组件
    - 实现 generateAccessToken()（HS256 算法，1 小时过期，至少 256 位密钥）
    - 实现 generateRefreshToken()（7 天过期）
    - 实现 validateToken() 令牌验证方法
    - 实现 refreshAccessToken() 令牌刷新方法
    - _Requirements: 3.1, 3.2, 3.6_

  - [ ] 10.2 实施令牌撤销机制
    - 创建 TokenRevocation ActiveRecord 模型（jti, user_id, revoked_at, expires_at, reason）
    - 实现 revokeToken() 和 revokeAllUserTokens()
    - 实现撤销列表检查（基于 jti）
    - _Requirements: 3.7, 8.4_

  - [ ] 10.3 实施密码重置令牌
    - 实现 generatePasswordResetToken()（使用 random_bytes(32)，至少 32 字节）
    - 实现令牌过期（1 小时）
    - 实现 validatePasswordResetToken() 和 invalidatePasswordResetToken()（一次性使用）
    - _Requirements: 3.3, 3.4, 3.5_

  - [ ] 10.4 更新认证端点
    - 更新登录端点返回访问令牌和刷新令牌
    - 创建令牌刷新端点
    - 更新登出端点调用 revokeAllUserTokens()
    - _Requirements: 3.6, 3.7_

  - [ ]* 10.5 为令牌管理编写属性测试
    - **Property 11: JWT 令牌过期时间限制**
    - **Property 12: 密码重置令牌长度和随机性**
    - **Property 13: 密码重置令牌过期时间**
    - **Property 14: 密码重置令牌一次性使用**
    - **Property 15: 登出撤销所有令牌**
    - **Validates: Requirements 3.2, 3.3, 3.4, 3.5, 3.7**

- [ ] 11. 实施认证增强
  - [ ] 11.1 创建账户锁定逻辑
    - 创建 FailedLoginAttempt ActiveRecord 模型（username, ip_address, attempted_at, user_agent）
    - 实现账户锁定逻辑（15 分钟内 5 次失败后锁定 30 分钟）
    - 实现 isAccountLocked() 和 unlockAccount() 方法
    - _Requirements: 3.8_

  - [ ] 11.2 更新认证控制器集成锁定检查
    - 在登录流程中集成账户锁定检查
    - 记录失败的登录尝试（IP 地址、时间戳、用户名）
    - 添加审计日志记录
    - _Requirements: 3.8, 3.9_

  - [ ]* 11.3 为认证增强编写属性测试
    - **Property 16: 失败登录审计记录**
    - **Validates: Requirements 3.9**

- [ ] 12. 实施错误处理和日志记录
  - [ ] 12.1 创建 ErrorHandler 组件
    - 实现 handleException() 区分生产/开发模式
    - 实现 formatErrorResponse()（生产环境返回通用消息 + request_id，开发环境包含详细信息）
    - 实现 filterSensitiveData() 过滤密码、令牌、API 密钥等
    - 实现 logError() 记录详细信息到安全日志文件
    - 实现 sendAlert() 关键错误管理员告警
    - _Requirements: 1.7, 9.1, 9.2, 9.3, 9.7_

  - [ ] 12.2 配置 Yii2 错误处理
    - 更新 errorHandler 组件配置
    - 配置日志目标使用 SafeFileTarget
    - 确保生产环境禁用 debug 模式和详细错误报告
    - _Requirements: 9.1, 9.2, 9.3, 11.5_

  - [ ]* 12.3 为错误处理编写属性测试
    - **Property 4: 错误消息不泄露敏感配置**
    - **Property 38: 生产环境通用错误消息**
    - **Property 39: 错误详细日志记录**
    - **Property 40: 错误响应不含敏感信息**
    - **Property 42: 关键错误管理员告警**
    - **Validates: Requirements 1.7, 9.1, 9.2, 9.3, 9.7**

- [ ] 13. Checkpoint - 验证 P1 实施
  - 运行所有测试确保通过
  - 测试文件上传沙箱和访问控制
  - 测试 CORS 配置
  - 测试 JWT 令牌刷新和撤销
  - 测试账户锁定
  - 测试错误处理
  - Ensure all tests pass, ask the user if questions arise.

### Phase 3: P2 - 中优先级（完善安全体系）

- [ ] 14. 实施密码历史检查
  - [ ] 14.1 创建 PasswordHistory 模型和密码变更逻辑
    - 创建 PasswordHistory ActiveRecord 模型（user_id, password_hash, created_at）
    - 在 User 模型中集成密码历史检查（checkPasswordHistory，保留最近 5 个）
    - 保存新密码到历史表
    - 实现管理员账户密码过期检查（90 天）
    - _Requirements: 5.3, 5.5_

  - [ ]* 14.2 为密码历史编写属性测试
    - **Property 22: 密码历史检查**
    - **Validates: Requirements 5.3**

- [ ] 15. 实施输入验证和清理
  - [ ] 15.1 创建 InputSanitizer 组件
    - 实现 sanitizeString()（移除 null 字节、控制字符）
    - 实现 sanitizeFilename()（移除路径分隔符和特殊字符，限制 255 字符）
    - 实现 validateType() 数据类型验证
    - 实现 sanitizeHtml()（使用 HTMLPurifier）
    - 实现 validatePath()（检测 ../, ..\, 绝对路径等路径遍历模式）
    - 实现 sanitizeSqlIdentifier()（仅允许字母、数字和下划线）
    - _Requirements: 6.1, 6.2, 6.4, 6.5, 6.6_

  - [ ] 15.2 创建 JSON Schema 验证器
    - 实现 JSON schema 验证方法
    - 为常见 API 端点定义 schema
    - _Requirements: 6.7_

  - [ ] 15.3 创建输入清理行为并集成到控制器
    - 创建 Yii2 行为封装 InputSanitizer
    - 应用到所有 API 控制器
    - 确保所有 SQL 查询使用参数化查询
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7_

  - [ ]* 15.4 为输入清理编写属性测试
    - **Property 25: 输入数据类型验证**
    - **Property 26: 文件名特殊字符清理**
    - **Property 27: HTML 输出编码**
    - **Property 28: 路径遍历模式拒绝**
    - **Property 29: Null 字节和控制字符拒绝**
    - **Property 30: JSON Schema 验证**
    - **Validates: Requirements 6.1, 6.2, 6.4, 6.5, 6.6, 6.7**

- [ ] 16. 实施 CSRF 和 XSS 防护
  - [ ] 16.1 配置 CSRF 保护
    - 启用 Yii2 CSRF 验证用于所有状态变更操作
    - 配置 SameSite cookie 属性
    - 实现 Origin/Referer 头验证用于敏感操作
    - _Requirements: 7.1, 7.2, 7.4, 7.6_

  - [ ] 16.2 实施 XSS 防护
    - 配置 HTMLPurifier 用于用户输入清理
    - 更新视图使用安全输出方法（Html::encode）
    - 更新邮件模板清理所有动态内容
    - 实现上下文感知输出编码（HTML、JavaScript、URL 上下文）
    - _Requirements: 10.1, 10.2, 10.7_

  - [ ] 16.3 配置安全响应头
    - 实现响应头中间件（on beforeSend 事件）
    - 配置 Content-Security-Policy 头
    - 配置 X-XSS-Protection: 1; mode=block
    - 配置 X-Content-Type-Options: nosniff
    - 配置 Strict-Transport-Security (HSTS)
    - 配置 X-Frame-Options: DENY
    - 配置 Referrer-Policy: strict-origin-when-cross-origin
    - 配置 Permissions-Policy
    - 确保 JSON 响应设置 Content-Type: application/json
    - _Requirements: 10.3, 10.4, 10.5, 10.6, 11.4_

  - [ ]* 16.4 为 CSRF 保护编写属性测试
    - **Property 31: 状态变更操作 CSRF 验证**
    - **Property 32: CSRF 令牌唯一性**
    - **Property 33: Origin 和 Referer 头验证**
    - **Validates: Requirements 7.1, 7.2, 7.6**

  - [ ]* 16.5 为 XSS 防护编写属性测试
    - **Property 43: HTML 用户数据编码**
    - **Property 44: 邮件模板动态内容清理**
    - **Property 45: JSON 响应 Content-Type 头**
    - **Property 46: 上下文感知输出编码**
    - **Validates: Requirements 10.1, 10.2, 10.6, 10.7**

- [ ] 17. 实施会话管理增强
  - [ ] 17.1 更新会话和令牌配置
    - 配置会话超时（30 分钟不活动）
    - 实现认证时会话 ID 重新生成
    - 配置安全 cookie 设置（HttpOnly, Secure, SameSite）
    - 实现 JWT 令牌滑动过期刷新机制
    - _Requirements: 8.1, 8.2, 8.6_

  - [ ] 17.2 实施密码变更会话失效
    - 在密码变更时调用 revokeAllUserTokens() 撤销所有令牌
    - 强制重新登录
    - _Requirements: 8.5_

  - [ ] 17.3 实施可疑活动检测和会话终止
    - 检测异常登录模式（IP 变化、地理位置异常等）
    - 自动终止可疑会话
    - _Requirements: 8.7_

  - [ ]* 17.4 为会话管理编写属性测试
    - **Property 34: 认证生成新会话标识符**
    - **Property 35: 过期令牌拒绝**
    - **Property 36: 令牌撤销列表维护**
    - **Property 37: 密码变更撤销所有会话**
    - **Validates: Requirements 8.1, 8.3, 8.4, 8.5**

- [ ] 18. Checkpoint - 验证 P2 前半段实施
  - 运行所有测试确保通过
  - 测试密码历史检查
  - 测试输入清理
  - 测试 CSRF/XSS 防护
  - 测试会话管理
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 19. 实施全面审计日志
  - [ ] 19.1 创建 AuditLogger 组件
    - 实现 logAuthEvent()（认证事件：登录尝试、成功、失败）
    - 实现 logAuthorizationFailure()（授权失败：操作和用户）
    - 实现 logFileUpload()（文件上传：文件元数据和用户信息）
    - 实现 logDataAccess()（敏感数据访问：用户和时间戳）
    - 实现 logConfigChange()（配置变更：用户、时间戳、旧值、新值）
    - 实现 logSecurityEvent()（通用安全事件）
    - 使用 JSON 格式日志（timestamp, event_type, user_id, ip_address, action, context）
    - _Requirements: 9.4, 11.2, 12.1, 12.2, 12.3, 12.4_

  - [ ] 19.2 创建 AuditLog ActiveRecord 模型
    - 实现数据模型（event_type, user_id, ip_address, action, resource, context, created_at）
    - 实现查询和报告方法
    - _Requirements: 9.4, 12.1, 12.2, 12.3, 12.4_

  - [ ] 19.3 配置日志轮换和保护
    - 配置日志文件每日轮换
    - 设置日志保留期（90 天）
    - 设置文件权限（600，仅应用用户可读写）
    - 实现防篡改机制（可选日志签名）
    - _Requirements: 9.5, 9.6, 12.5_

  - [ ] 19.4 集成审计日志到关键操作
    - 在认证端点添加审计日志
    - 在授权检查添加审计日志
    - 在文件上传添加审计日志
    - 在敏感数据访问添加审计日志
    - 在安全配置变更添加审计日志
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 11.2_

  - [ ]* 19.5 为审计日志编写属性测试
    - **Property 41: 安全事件完整审计**
    - **Property 47: 安全配置变更审计**
    - **Property 48: 认证事件审计记录**
    - **Property 49: 授权失败审计记录**
    - **Property 50: 文件上传审计记录**
    - **Property 51: 敏感数据访问审计记录**
    - **Validates: Requirements 9.4, 11.2, 12.1, 12.2, 12.3, 12.4**

- [ ] 20. 实施安全配置管理
  - [ ] 20.1 创建 SecurityConfig 模型和集中配置
    - 创建 SecurityConfig ActiveRecord 模型（key, value, updated_by, updated_at）
    - 实现集中安全配置文件
    - 实现配置读取和更新方法
    - _Requirements: 11.1_

  - [ ] 20.2 实施启动时配置验证
    - 创建启动时配置验证脚本
    - 验证所有必需的安全设置存在
    - 验证生产环境禁用 debug 模式
    - 实现自动化安全配置检查
    - _Requirements: 11.3, 11.5, 11.6_

- [ ] 21. 实施监控和告警
  - [ ] 21.1 配置监控指标收集
    - 实现认证指标收集（失败登录率、账户锁定数量、密码重置请求数量）
    - 实现 API 指标收集（速率限制触发次数、401/403 错误率）
    - 实现文件上传指标收集（上传失败率、被拒绝的文件类型）
    - 实现安全事件指标收集（XSS/SQL 注入/路径遍历尝试次数）
    - _Requirements: 12.6_

  - [ ] 21.2 配置告警规则
    - 配置高优先级告警（5 分钟内超过 10 次失败登录、SQL 注入尝试、路径遍历尝试）
    - 配置中优先级告警（1 小时内超过 100 次速率限制触发、异常高错误率）
    - 配置低优先级告警（日志文件大小超过阈值、令牌撤销列表大小超过阈值）
    - _Requirements: 12.7_

- [ ] 22. 代码质量和安全扫描
  - [ ] 22.1 运行静态分析和依赖扫描
    - 使用 PHPStan 或 Psalm 扫描代码并修复问题
    - 运行 composer audit 检查已知漏洞并更新依赖
    - _Requirements: All_

- [ ] 23. Final Checkpoint - 完整验证
  - 运行所有单元测试和属性测试
  - 验证所有安全功能正常工作
  - 检查代码覆盖率（目标 80%）
  - 进行最终安全审查
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- 任务标记 `*` 的为可选任务，可以跳过以加快 MVP 开发
- 每个任务都引用了具体的需求以确保可追溯性
- Checkpoint 任务确保增量验证
- 属性测试验证通用正确性属性（对应设计文档中的 Property 1-51）
- 单元测试验证特定示例和边界条件
- 建议按阶段顺序执行，确保关键安全问题优先解决
- 所有属性测试应使用 eris/eris 库，配置为至少运行 100 次迭代
- 测试标签格式: `Feature: backend-security-hardening, Property N: {property_text}`

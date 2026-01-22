# Implementation Plan: Backend Security Hardening

## Overview

本实施计划将 Yii2 后端应用的安全加固工作分解为可执行的任务。实施分为三个阶段（P0、P1、P2），按优先级逐步完成。每个任务都引用了相应的需求，确保完整的可追溯性。

## Tasks

### Phase 1: P0 - 立即修复（关键安全问题）

- [ ] 1. 设置安全基础设施
  - 创建数据库迁移文件用于新的安全表
  - 设置 Redis 用于速率限制和令牌撤销
  - 创建安全配置文件结构
  - _Requirements: 1.4, 4.1, 8.4_

- [ ] 2. 实施凭证管理器
  - [ ] 2.1 创建 CredentialManager 组件
    - 实现从环境变量加载配置的方法
    - 实现配置验证方法
    - 实现 JWT 密钥管理和轮换
    - _Requirements: 1.1, 1.2, 1.4, 1.6_
  
  - [ ] 2.2 为凭证管理器编写属性测试
    - **Property 1: 环境变量验证完整性**
    - **Validates: Requirements 1.2**
  
  - [ ] 2.3 为凭证管理器编写属性测试
    - **Property 3: JWT 密钥轮换保持向后兼容**
    - **Validates: Requirements 1.6**

- [ ] 3. 实施敏感信息保护
  - [ ] 3.1 更新 .gitignore 文件
    - 确保所有 .env 文件被忽略
    - 添加日志文件和临时文件模式
    - _Requirements: 1.5_
  
  - [ ] 3.2 从配置文件中移除硬编码凭证
    - 更新 files/common/config/main-local.php 使用环境变量
    - 更新 .env.docker 为模板文件
    - 创建 .env.example 文件
    - _Requirements: 1.1, 1.4_
  
  - [ ] 3.3 实施日志敏感信息过滤
    - 创建 LogFilter 组件
    - 实现敏感数据模式匹配和替换
    - 集成到 Yii2 日志组件
    - _Requirements: 1.3_
  
  - [ ] 3.4 为日志过滤编写属性测试
    - **Property 2: 敏感数据日志过滤**
    - **Validates: Requirements 1.3**

- [ ] 4. 实施文件上传安全验证
  - [ ] 4.1 创建 UploadValidator 组件
    - 定义 MIME 类型白名单
    - 定义文件扩展名白名单
    - 实现文件大小验证（10MB 限制）
    - 实现 MIME 类型验证
    - 实现文件扩展名验证
    - _Requirements: 2.1, 2.2, 2.3_
  
  - [ ] 4.2 实施文件内容验证
    - 使用 finfo_file() 验证实际内容类型
    - 实现双扩展名检测
    - 实现安全文件名生成
    - _Requirements: 2.4, 2.5, 2.8_
  
  - [ ] 4.3 更新 UploadController
    - 集成 UploadValidator
    - 添加验证失败的审计日志
    - 更新错误响应
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.8, 2.9_
  
  - [ ] 4.4 为文件上传验证编写属性测试
    - **Property 5: MIME 类型白名单验证**
    - **Property 6: 文件扩展名白名单验证**
    - **Property 7: 文件内容与声明类型匹配**
    - **Validates: Requirements 2.1, 2.2, 2.4_
  
  - [ ] 4.5 为文件名生成编写属性测试
    - **Property 8: 生成的文件名无路径遍历**
    - **Property 9: 双扩展名文件拒绝**
    - **Validates: Requirements 2.5, 2.8**
  
  - [ ] 4.6 为文件上传审计编写属性测试
    - **Property 10: 文件上传失败记录审计**
    - **Validates: Requirements 2.9**

- [ ] 5. 实施 API 速率限制
  - [ ] 5.1 创建 RateLimiter 组件
    - 实现基于 Redis 的滑动窗口算法
    - 实现 IP 地址速率限制（100/分钟）
    - 实现用户速率限制（1000/小时）
    - 实现登录端点特殊限制（5/15分钟）
    - _Requirements: 4.1, 4.2_
  
  - [ ] 5.2 创建 RateLimitBehavior
    - 实现 Yii2 行为接口
    - 添加速率限制响应头
    - 实现 HTTP 429 响应
    - _Requirements: 4.1, 4.2, 4.3_
  
  - [ ] 5.3 将速率限制应用到 API 控制器
    - 更新 API 基础控制器
    - 配置不同端点的限制策略
    - _Requirements: 4.1, 4.2_
  
  - [ ] 5.4 为速率限制编写单元测试
    - 测试边界条件（第 100 和 101 个请求）
    - 测试响应头
    - _Requirements: 4.1, 4.2_
  
  - [ ] 5.5 为速率限制编写属性测试
    - **Property 17: 速率限制超限响应**
    - **Validates: Requirements 4.3**

- [ ] 6. 强化密码策略
  - [ ] 6.1 更新 User 模型密码验证
    - 增加最小长度到 12 字符
    - 添加复杂度验证规则
    - 实现弱密码列表检查
    - _Requirements: 5.1, 5.2, 5.6, 5.7_
  
  - [ ] 6.2 更新密码哈希方法
    - 确保使用 password_hash() 和 PASSWORD_BCRYPT
    - 设置 cost 因子为 12
    - _Requirements: 5.4_
  
  - [ ] 6.3 为密码验证编写属性测试
    - **Property 21: 密码复杂度验证**
    - **Property 23: 密码哈希算法验证**
    - **Property 24: 弱密码拒绝**
    - **Validates: Requirements 5.2, 5.4, 5.6, 5.7**

- [ ] 7. Checkpoint - 验证 P0 实施
  - 运行所有测试确保通过
  - 验证环境变量配置正确
  - 测试文件上传安全性
  - 测试速率限制功能
  - 测试密码策略
  - 询问用户是否有问题

### Phase 2: P1 - 高优先级（重要安全增强）

- [ ] 8. 实施文件上传沙箱
  - [ ] 8.1 配置安全文件存储
    - 创建 web root 外的上传目录
    - 设置适当的文件权限
    - 更新 UploadValidator 使用新路径
    - _Requirements: 2.6_
  
  - [ ] 8.2 创建文件服务控制器
    - 实现访问控制检查
    - 实现安全文件下载
    - 添加 Content-Type 和 Content-Disposition 头
    - _Requirements: 2.7_
  
  - [ ] 8.3 为文件访问控制编写属性测试
    - **Property 7: 文件访问需要认证**
    - **Validates: Requirements 2.7**

- [ ] 9. 配置 CORS 安全策略
  - [ ] 9.1 创建 CorsManager 组件
    - 从环境变量读取允许的来源白名单
    - 实现来源验证方法
    - 定义允许的方法和头部
    - _Requirements: 4.4, 4.5, 4.6_
  
  - [ ] 9.2 创建 CORS 过滤器
    - 实现 Yii2 过滤器接口
    - 处理预检请求
    - 设置 CORS 响应头
    - _Requirements: 4.4, 4.5, 4.6_
  
  - [ ] 9.3 更新 API 配置
    - 移除 files/api/config/main.php 中的通配符 CORS
    - 应用 CORS 过滤器到 API 控制器
    - 配置生产环境的域白名单
    - _Requirements: 4.4, 4.5_
  
  - [ ] 9.4 为 CORS 编写属性测试
    - **Property 18: CORS 非白名单域拒绝**
    - **Property 19: CORS 非允许方法拒绝**
    - **Validates: Requirements 4.4, 4.6**

- [ ] 10. 实施 JWT 令牌管理
  - [ ] 10.1 创建 TokenManager 组件
    - 实现 JWT 访问令牌生成（1 小时过期）
    - 实现 JWT 刷新令牌生成（7 天过期）
    - 实现令牌验证方法
    - 实现令牌刷新方法
    - _Requirements: 3.1, 3.2, 3.6_
  
  - [ ] 10.2 实施令牌撤销机制
    - 创建 TokenRevocation 模型
    - 实现令牌撤销方法
    - 实现撤销列表检查
    - 实现用户所有令牌撤销
    - _Requirements: 3.7, 8.4_
  
  - [ ] 10.3 实施密码重置令牌
    - 实现安全令牌生成（32 字节）
    - 实现令牌过期（1 小时）
    - 实现一次性使用验证
    - _Requirements: 3.3, 3.4, 3.5_
  
  - [ ] 10.4 更新认证端点
    - 更新登录端点返回访问和刷新令牌
    - 创建令牌刷新端点
    - 更新登出端点撤销令牌
    - _Requirements: 3.6, 3.7_
  
  - [ ] 10.5 为令牌管理编写属性测试
    - **Property 11: JWT 令牌过期时间限制**
    - **Property 12: 密码重置令牌长度和随机性**
    - **Property 13: 密码重置令牌过期时间**
    - **Property 14: 密码重置令牌一次性使用**
    - **Property 15: 登出撤销所有令牌**
    - **Validates: Requirements 3.2, 3.3, 3.4, 3.5, 3.7**

- [ ] 11. 实施认证增强
  - [ ] 11.1 创建 AuthManager 组件
    - 实现账户锁定逻辑（5 次失败/15 分钟）
    - 实现失败登录记录
    - 实现账户解锁方法
    - _Requirements: 3.8_
  
  - [ ] 11.2 创建 FailedLoginAttempt 模型
    - 实现数据模型和验证规则
    - 实现查询方法
    - 实现清理过期记录方法
    - _Requirements: 3.8, 3.9_
  
  - [ ] 11.3 更新 SiteController 认证逻辑
    - 集成账户锁定检查
    - 记录失败的登录尝试
    - 添加审计日志
    - _Requirements: 3.8, 3.9_
  
  - [ ] 11.4 为认证增强编写单元测试
    - 测试账户锁定边界条件
    - 测试锁定过期
    - _Requirements: 3.8_
  
  - [ ] 11.5 为失败登录编写属性测试
    - **Property 16: 失败登录审计记录**
    - **Validates: Requirements 3.9**

- [ ] 12. 实施错误处理和日志记录
  - [ ] 12.1 创建 ErrorHandler 组件
    - 实现生产/开发模式错误格式化
    - 实现敏感信息过滤
    - 实现错误日志记录
    - 实现关键错误告警
    - _Requirements: 9.1, 9.2, 9.3, 9.7_
  
  - [ ] 12.2 配置 Yii2 错误处理
    - 更新 errorHandler 组件配置
    - 设置自定义错误视图
    - 配置日志目标
    - _Requirements: 9.1, 9.2, 9.3_
  
  - [ ] 12.3 为错误处理编写属性测试
    - **Property 4: 错误消息不泄露敏感配置**
    - **Property 38: 生产环境通用错误消息**
    - **Property 39: 错误详细日志记录**
    - **Property 40: 错误响应不含敏感信息**
    - **Property 42: 关键错误管理员告警**
    - **Validates: Requirements 1.7, 9.1, 9.2, 9.3, 9.7**

- [ ] 13. Checkpoint - 验证 P1 实施
  - 运行所有测试确保通过
  - 测试文件上传沙箱
  - 测试 CORS 配置
  - 测试 JWT 令牌刷新和撤销
  - 测试账户锁定
  - 测试错误处理
  - 询问用户是否有问题

### Phase 3: P2 - 中优先级（完善安全体系）

- [ ] 14. 实施密码历史检查
  - [ ] 14.1 创建 PasswordHistory 模型
    - 实现数据模型和验证规则
    - 实现查询方法
    - 实现清理旧记录方法（保留最近 5 个）
    - _Requirements: 5.3_
  
  - [ ] 14.2 更新密码变更逻辑
    - 在 User 模型中集成密码历史检查
    - 保存新密码到历史表
    - 实现密码过期检查（管理员 90 天）
    - _Requirements: 5.3, 5.5_
  
  - [ ]* 14.3 为密码历史编写属性测试
    - **Property 22: 密码历史检查**
    - **Validates: Requirements 5.3**

- [ ] 15. 实施输入验证和清理
  - [ ] 15.1 创建 InputSanitizer 组件
    - 实现字符串清理方法
    - 实现文件名清理方法
    - 实现数据类型验证
    - 实现 HTML 清理（使用 HTMLPurifier）
    - 实现路径验证
    - _Requirements: 6.1, 6.2, 6.4, 6.5, 6.6_
  
  - [ ] 15.2 创建 JSON Schema 验证器
    - 实现 JSON schema 验证方法
    - 为常见 API 端点��义 schema
    - _Requirements: 6.7_
  
  - [ ] 15.3 集成输入清理到控制器
    - 创建输入清理行为
    - 应用到所有 API 控制器
    - _Requirements: 6.1, 6.2, 6.4, 6.5, 6.6, 6.7_
  
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
    - 启用 Yii2 CSRF 验证
    - 配置 SameSite cookie 属性
    - 实现 Origin/Referer 头验证
    - _Requirements: 7.1, 7.2, 7.4, 7.6_
  
  - [ ] 16.2 实施 XSS 防护
    - 配置 HTMLPurifier
    - 更新视图使用安全输出方法
    - 更新邮件模板清理动态内容
    - _Requirements: 10.1, 10.2_
  
  - [ ] 16.3 配置安全响应头
    - 实现响应头中间件
    - 配置 CSP、X-XSS-Protection、X-Content-Type-Options 等
    - 配置 HSTS、X-Frame-Options、Referrer-Policy
    - _Requirements: 10.3, 10.4, 10.5, 11.4_
  
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
  - [ ] 17.1 更新会话配置
    - 配置会话超时（30 分钟不活动）
    - 实现会话 ID 重新生成
    - 配置安全 cookie 设置
    - _Requirements: 8.1, 8.6_
  
  - [ ] 17.2 实施密码变更会话失效
    - 在密码变更时撤销所有令牌
    - 强制重新登录
    - _Requirements: 8.5_
  
  - [ ]* 17.3 为会话管理编写属性测试
    - **Property 34: 认证生成新会话标识符**
    - **Property 35: 过期令牌拒绝**
    - **Property 36: 令牌撤销列表维护**
    - **Property 37: 密码变更撤销所有会话**
    - **Validates: Requirements 8.1, 8.3, 8.4, 8.5**

- [ ] 18. 实施全面审计日志
  - [ ] 18.1 创建 AuditLogger 组件
    - 实现认证事件日志方法
    - 实现授权失败日志方法
    - 实现文件上传日志方法
    - 实现敏感数据访问日志方法
    - 实现配置变更日志方法
    - _Requirements: 9.4, 11.2, 12.1, 12.2, 12.3, 12.4_
  
  - [ ] 18.2 创建 AuditLog 模型
    - 实现数据模型和验证规则
    - 实现查询和报告方法
    - _Requirements: 9.4, 12.1, 12.2, 12.3, 12.4_
  
  - [ ] 18.3 配置日志轮换和保护
    - 配置日志文件轮换（每日）
    - 设置日志保留期（90 天）
    - 设置文件权限（600）
    - _Requirements: 9.5, 9.6_
  
  - [ ] 18.4 集成审计日志到关键操作
    - 在认证端点添加审计日志
    - 在授权检查添加审计日志
    - 在文件上传添加审计日志
    - 在敏感数据访问添加审计日志
    - _Requirements: 12.1, 12.2, 12.3, 12.4_
  
  - [ ]* 18.5 为审计日志编写属性测试
    - **Property 41: 安全事件完整审计**
    - **Property 47: 安全配置变更审计**
    - **Property 48: 认证事件审计记录**
    - **Property 49: 授权失败审计记录**
    - **Property 50: 文件上传审计记录**
    - **Property 51: 敏感数据访问审计记录**
    - **Validates: Requirements 9.4, 11.2, 12.1, 12.2, 12.3, 12.4**

- [ ] 19. 实施安全配置管理
  - [ ] 19.1 创建 SecurityConfig 模型
    - 实现数据模型和验证规则
    - 实现配置读取和更新方法
    - _Requirements: 11.1_
  
  - [ ] 19.2 实施配置验证
    - 创建启动时配置验证脚本
    - 验证所有必需的安全设置
    - 验证生产环境配置
    - _Requirements: 11.3, 11.5_
  
  - [ ] 19.3 创建安全配置文档
    - 编写环境变量配置指南
    - 编写安全最佳实践文档
    - 创建配置检查清单
    - _Requirements: 11.7_

- [ ] 20. 实施监控和告警
  - [ ] 20.1 配置监控指标收集
    - 实现认证指标收集
    - 实现 API 指标收集
    - 实现文件上传指标收集
    - 实现安全事件指标收集
    - _Requirements: 12.6_
  
  - [ ] 20.2 配置告警规则
    - 配置高优先级告警（失败登录、注入尝试）
    - 配置中优先级告警（速率限制、错误率）
    - 配置低优先级告警（日志大小、过期提醒）
    - _Requirements: 12.7_

- [ ] 21. 代码审查和安全扫描
  - [ ] 21.1 运行静态分析工具
    - 使用 PHPStan 或 Psalm 扫描代码
    - 修复发现的问题
    - _Requirements: All_
  
  - [ ] 21.2 运行依赖扫描
    - 运行 composer audit
    - 更新有漏洞的依赖
    - _Requirements: All_
  
  - [ ] 21.3 运行安全扫描
    - 使用 OWASP ZAP 或 Burp Suite 扫描
    - 修复发现的漏洞
    - _Requirements: All_

- [ ] 22. Final Checkpoint - 完整验证
  - 运行所有单元测试和属性测试
  - 验证所有安全功能正常工作
  - 检查代码覆盖率（目标 80%）
  - 验证所有文档完整
  - 进行最终安全审查
  - 询问用户是否准备部署

## Notes

- 任务标记 `*` 的为可选任务，可以跳过以加快 MVP 开发
- 每个任务都引用了具体的需求以确保可追溯性
- Checkpoint 任务确保增量验证
- 属性测试验证通用正确性属性
- 单元测试验证特定示例和边界条件
- 建议按阶段顺序执行，确保关键安全问题优先解决
- 每个阶段完成后应进行全面测试和验证
- 所有属性测试应配置为至少运行 100 次迭代

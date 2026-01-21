# 项目文档索引

本目录包含项目的所有文档，按功能分类组织。

## 📁 目录结构

```
docs/
├── README.md                          # 本文件 - 文档索引
├── DOCUMENTATION_ORGANIZATION.md      # 文档整理说明
├── SCRIPTS_ORGANIZATION.md            # 脚本整理说明
├── CI-LOOP-STATUS.md                  # CI 循环状态
├── docker/                            # Docker 相关文档
│   ├── DOCKER_QUICK_START.md         # Docker 快速启动指南
│   └── DOCKER_SETUP_COMPLETE.md      # Docker 设置完成说明
├── email/                             # 邮件功能文档
│   ├── EMAIL_CONFIG_GUIDE.md         # 邮件配置指南
│   ├── EMAIL_FUNCTIONALITY_GUIDE.md  # 邮件功能指南
│   ├── EMAIL_TEST_RESULTS.md         # 邮件测试结果
│   ├── GET_SMTP_AUTH_CODE.md         # SMTP 授权码获取指南
│   └── 邮件功能快速指南.md            # 邮件功能快速指南（中文）
├── security/                          # 安全相关文档
│   └── SECURITY_AUDIT_SUMMARY.md     # 安全审查总结
├── sessions/                          # 会话记录
│   └── SESSION_SUMMARY_2026-01-21.md # 2026-01-21 会话总结
├── API_HEALTH_VERSION.md              # API 健康检查和版本端点
├── OPENAPI_CONTROLLERS_STATUS.md      # OpenAPI 控制器状态
├── SWAGGER_CONFIG.md                  # Swagger 配置
└── SWAGGER_DEPLOYMENT.md              # Swagger 部署指南
```

## 📚 文档分类

### 🐳 Docker 文档
快速启动和配置 Docker 环境的指南。

- [Docker 快速启动指南](docker/DOCKER_QUICK_START.md) - 快速启动 Docker 容器
- [Docker 设置完成说明](docker/DOCKER_SETUP_COMPLETE.md) - Docker 环境配置完成后的说明

### 📧 邮件功能文档
邮件系统的配置、测试和使用指南。

- [邮件配置指南](email/EMAIL_CONFIG_GUIDE.md) - 详细的邮件配置步骤
- [邮件功能指南](email/EMAIL_FUNCTIONALITY_GUIDE.md) - 邮件功能使用说明
- [邮件测试结果](email/EMAIL_TEST_RESULTS.md) - 邮件功能测试报告
- [SMTP 授权码获取](email/GET_SMTP_AUTH_CODE.md) - 如何获取 SMTP 授权码
- [邮件功能快速指南](email/邮件功能快速指南.md) - 中文快速指南

### 🔒 安全文档
安全审查、加固和最佳实践。

- [安全审查总结](security/SECURITY_AUDIT_SUMMARY.md) - 完整的安全审查报告
- [安全加固规范](.kiro/specs/backend-security-hardening/) - 详细的安全加固实施计划

### 📖 API 文档
API 相关的配置和文档。

- [API 健康检查和版本](API_HEALTH_VERSION.md) - API 健康检查和版本端点说明
- [OpenAPI 控制器状态](OPENAPI_CONTROLLERS_STATUS.md) - OpenAPI 注解状态
- [Swagger 配置](SWAGGER_CONFIG.md) - Swagger UI 配置指南
- [Swagger 部署](SWAGGER_DEPLOYMENT.md) - Swagger 部署说明

### 📝 会话记录
开发会话的总结和记录。

- [2026-01-21 会话总结](sessions/SESSION_SUMMARY_2026-01-21.md) - 安全审查会话记录

### 🔧 CI/CD 文档
持续集成和部署相关文档。

- [CI 循环状态](CI-LOOP-STATUS.md) - CI 循环状态和配置

### 📜 脚本文档
Shell 脚本的使用和开发指南。

- [脚本使用指南](../scripts/README.md) - 所有脚本的详细说明
- [脚本整理说明](SCRIPTS_ORGANIZATION.md) - 脚本整理过程记录

### 📋 项目管理文档
项目整理和组织相关文档。

- [文档整理说明](DOCUMENTATION_ORGANIZATION.md) - 文档整理过程记录
- [脚本整理说明](SCRIPTS_ORGANIZATION.md) - 脚本整理过程记录

## 🚀 快速导航

### 新手入门
1. [Docker 快速启动](docker/DOCKER_QUICK_START.md)
2. [脚本使用指南](../scripts/README.md)
3. [邮件功能快速指南](email/邮件功能快速指南.md)
4. [API 健康检查](API_HEALTH_VERSION.md)

### 开发者指南
1. [Swagger 配置](SWAGGER_CONFIG.md)
2. [OpenAPI 控制器状态](OPENAPI_CONTROLLERS_STATUS.md)
3. [邮件功能指南](email/EMAIL_FUNCTIONALITY_GUIDE.md)

### 运维指南
1. [Docker 设置完成说明](docker/DOCKER_SETUP_COMPLETE.md)
2. [邮件配置指南](email/EMAIL_CONFIG_GUIDE.md)
3. [安全审查总结](security/SECURITY_AUDIT_SUMMARY.md)

## 📋 相关资源

### 规范文档
- [邮箱验证和密码重置规范](.kiro/specs/email-verification-and-password-reset/)
- [后端安全加固规范](.kiro/specs/backend-security-hardening/)
- [OpenAPI 3 实施规范](.kiro/specs/openapi-3-implementation/)

### 项目文档
- [项目 README](../README.md)
- [脚本使用指南](../scripts/README.md)
- [高级模板文档](../advanced/docs/)
- [Docker 文档](../docker/README.md)

## 🔄 文档更新

文档会随着项目的发展持续更新。如果发现文档过时或有错误，请：

1. 检查最新的规范文档（`.kiro/specs/`）
2. 查看会话记录（`sessions/`）了解最新变更
3. 参考项目 README 获取最新信息

---

**最后更新**: 2026-01-21  
**维护者**: 开发团队

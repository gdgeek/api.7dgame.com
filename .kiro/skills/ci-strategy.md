---
inclusion: fileMatch
fileMatchPattern: '**/.github/workflows/*.yml'
---

# CI/CD 构建策略

## Docker 镜像构建规则

项目使用腾讯云镜像仓库 `hkccr.ccs.tencentyun.com/gdgeek/api`。

### 所有分支（push 触发）
- 运行测试（PHPUnit）
- 构建 Docker 镜像并推送两个 tag：
  - `短hash`（7位 commit hash）
  - `分支名`（如 master、develop）

### 主分支（master/main）额外操作
- 额外推送 `latest` tag
- 触发 Portainer Webhook 自动部署到生产环境

### 非主分支（如 develop）
- 只推送 `短hash` 和 `分支名` tag
- 不推送 `latest`，不触发自动部署
- 开发环境可通过分支名 tag（如 `api:develop`）手动配置部署

## 技术细节
- PHP 版本：CI 和 Docker 生产镜像使用 8.5，composer.json 兼容 >=8.4
- 分支名中的 `/` 会被替换为 `-`（如 `feature/foo` → `feature-foo`）
- CI 的 Prepare Test Configs 步骤会覆盖配置文件为空数组，测试不能直接 require 配置文件

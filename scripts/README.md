# 脚本目录

本目录包含项目的所有 Shell 脚本，按功能分类组织。

## 📁 目录结构

```
scripts/
├── README.md           # 本文件 - 脚本索引
├── docker/             # Docker 相关脚本
│   ├── start-docker.sh
│   ├── stop-docker.sh
│   └── check-env.sh
├── email/              # 邮件相关脚本
│   ├── configure-email.sh
│   └── update-smtp-auth-code.sh
└── ci/                 # CI/CD 相关脚本
    ├── check-ci-status.sh
    ├── check-ci.sh
    └── monitor-ci.sh
```

## 🐳 Docker 脚本

### start-docker.sh
**功能**: 一键启动 Docker 环境

**用法**:
```bash
./scripts/docker/start-docker.sh
```

**功能说明**:
- ✅ 检查并创建环境配置文件
- ✅ 生成 JWT 密钥
- ✅ 构建 Docker 镜像
- ✅ 启动所有服务
- ✅ 运行数据库迁移
- ✅ 初始化 RBAC 权限
- ✅ 设置文件权限

**相关文档**: [Docker 快速启动指南](../docs/docker/DOCKER_QUICK_START.md)

---

### stop-docker.sh
**功能**: 停止 Docker 服务

**用法**:
```bash
./scripts/docker/stop-docker.sh
```

**功能说明**:
- 停止所有 Docker 容器
- 可选择是否删除容器和卷

---

### check-env.sh
**功能**: 检查环境配置

**用法**:
```bash
./scripts/docker/check-env.sh
```

**功能说明**:
- 验证 `.env.docker` 文件是否存在
- 检查必需的环境变量
- 验证 JWT 密钥文件
- 显示配置状态

---

## 📧 邮件脚本

### configure-email.sh
**功能**: 配置邮件服务

**用法**:
```bash
./scripts/email/configure-email.sh
```

**功能说明**:
- 交互式配置邮件服务器
- 设置 SMTP 凭证
- 测试邮件连接
- 更新环境变量

**相关文档**: [邮件配置指南](../docs/email/EMAIL_CONFIG_GUIDE.md)

---

### update-smtp-auth-code.sh
**功能**: 更新 SMTP 授权码

**用法**:
```bash
./scripts/email/update-smtp-auth-code.sh [新授权码]
```

**功能说明**:
- 更新 `.env.docker` 中的 SMTP 授权码
- 重启相关服务
- 验证新配置

**相关文档**: [获取 SMTP 授权码](../docs/email/GET_SMTP_AUTH_CODE.md)

---

## 🔧 CI/CD 脚本

### check-ci-status.sh
**功能**: 检查 CI 状态

**用法**:
```bash
./scripts/ci/check-ci-status.sh
```

**功能说明**:
- 检查 CI 管道状态
- 显示最新构建结果
- 报告测试覆盖率

---

### check-ci.sh
**功能**: 运行 CI 检查

**用法**:
```bash
./scripts/ci/check-ci.sh
```

**功能说明**:
- 运行代码质量检查
- 执行单元测试
- 生成测试报告

---

### monitor-ci.sh
**功能**: 监控 CI 循环

**用法**:
```bash
./scripts/ci/monitor-ci.sh
```

**功能说明**:
- 持续监控 CI 状态
- 自动重试失败的构建
- 发送通知

**相关文档**: [CI 循环状态](../docs/CI-LOOP-STATUS.md)

---

## 🚀 快速开始

### 首次使用

1. **启动 Docker 环境**
```bash
./scripts/docker/start-docker.sh
```

2. **配置邮件服务**（可选）
```bash
./scripts/email/configure-email.sh
```

3. **检查环境配置**
```bash
./scripts/docker/check-env.sh
```

### 日常使用

**启动服务**:
```bash
./scripts/docker/start-docker.sh
```

**停止服务**:
```bash
./scripts/docker/stop-docker.sh
```

**更新邮件配置**:
```bash
./scripts/email/update-smtp-auth-code.sh YOUR_NEW_AUTH_CODE
```

**检查 CI 状态**:
```bash
./scripts/ci/check-ci-status.sh
```

## 📝 脚本开发规范

### 文件命名
- 使用小写字母和连字符
- 使用 `.sh` 扩展名
- 名称应清晰描述功能

### 脚本结构
```bash
#!/bin/bash

# 脚本说明
# 功能: 描述脚本功能
# 用法: ./script-name.sh [参数]

set -e  # 遇到错误立即退出

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 函数定义
function print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

function print_error() {
    echo -e "${RED}✗ $1${NC}"
}

function print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# 主逻辑
main() {
    print_info "开始执行..."
    # 脚本逻辑
    print_success "执行完成"
}

# 执行主函数
main "$@"
```

### 最佳实践

1. **错误处理**
   - 使用 `set -e` 在错误时退出
   - 提供清晰的错误消息
   - 使用退出码表示状态

2. **用户交互**
   - 提供清晰的提示信息
   - 使用颜色区分不同类型的消息
   - 在危险操作前请求确认

3. **文档**
   - 在脚本开头添加说明注释
   - 提供使用示例
   - 说明所需的环境变量

4. **可移植性**
   - 使用 `#!/bin/bash` 而非 `#!/bin/sh`
   - 避免使用特定平台的命令
   - 检查依赖是否存在

## 🔗 相关资源

### 文档
- [项目 README](../README.md)
- [Docker 文档](../docs/docker/)
- [邮件文档](../docs/email/)
- [CI/CD 文档](../docs/CI-LOOP-STATUS.md)

### 工具
- [Makefile](../Makefile) - 构建和管理命令
- [docker-compose.yml](../docker-compose.yml) - Docker 配置

## 🤝 贡献

添加新脚本时：

1. 将脚本放在适当的子目录
2. 添加执行权限：`chmod +x script-name.sh`
3. 更新本 README 文档
4. 在根 README 中添加链接（如果是常用脚本）
5. 提供使用示例和文档

## 📞 问题反馈

如果脚本执行遇到问题：

1. 检查脚本的执行权限
2. 查看相关文档
3. 检查环境变量配置
4. 提交 Issue 或联系开发团队

---

**最后更新**: 2026-01-21  
**维护者**: 开发团队

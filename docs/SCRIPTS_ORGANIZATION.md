# Shell 脚本整理说明

**整理日期**: 2026-01-21  
**整理人员**: Kiro AI Assistant

## 📋 整理概述

本次整理将根目录下的所有 Shell 脚本按功能分类，移动到 `scripts/` 目录下的相应子目录中，使项目结构更加清晰和易于维护。

## 🔄 脚本移动记录

### 🐳 Docker 脚本 → `scripts/docker/`

| 原路径 | 新路径 | 说明 |
|--------|--------|------|
| `start-docker.sh` | `scripts/docker/start-docker.sh` | 一键启动 Docker 环境 |
| `stop-docker.sh` | `scripts/docker/stop-docker.sh` | 停止 Docker 服务 |
| `check-env.sh` | `scripts/docker/check-env.sh` | 检查环境配置 |

### 📧 邮件脚本 → `scripts/email/`

| 原路径 | 新路径 | 说明 |
|--------|--------|------|
| `configure-email.sh` | `scripts/email/configure-email.sh` | 配置邮件服务 |
| `update-smtp-auth-code.sh` | `scripts/email/update-smtp-auth-code.sh` | 更新 SMTP 授权码 |

### 🔧 CI/CD 脚本 → `scripts/ci/`

| 原路径 | 新路径 | 说明 |
|--------|--------|------|
| `check-ci-status.sh` | `scripts/ci/check-ci-status.sh` | 检查 CI 状态 |
| `check-ci.sh` | `scripts/ci/check-ci.sh` | 运行 CI 检查 |
| `monitor-ci.sh` | `scripts/ci/monitor-ci.sh` | 监控 CI 循环 |

## 📂 最终目录结构

```
scripts/
├── README.md                      # 📖 脚本索引（新增）
│
├── docker/                        # 🐳 Docker 脚本
│   ├── start-docker.sh           # 一键启动（移动）
│   ├── stop-docker.sh            # 停止服务（移动）
│   └── check-env.sh              # 环境检查（移动）
│
├── email/                         # 📧 邮件脚本
│   ├── configure-email.sh        # 邮件配置（移动）
│   └── update-smtp-auth-code.sh  # 更新授权码（移动）
│
└── ci/                            # 🔧 CI/CD 脚本
    ├── check-ci-status.sh        # CI 状态（移动）
    ├── check-ci.sh               # CI 检查（移动）
    └── monitor-ci.sh             # CI 监控（移动）
```

## 📝 新增文档

- **`scripts/README.md`** - 脚本使用指南
  - 提供所有脚本的详细说明
  - 包含使用示例和参数说明
  - 提供脚本开发规范
  - 包含快速开始指南

## 🔗 更新的引用

### 根目录 README.md

已更新所有脚本引用，现在指向新的脚本位置：

```bash
# 旧路径
./start-docker.sh
./stop-docker.sh
./check-env.sh

# 新路径
./scripts/docker/start-docker.sh
./scripts/docker/stop-docker.sh
./scripts/docker/check-env.sh
```

### Makefile

Makefile 中的脚本引用也需要更新（如果有的话）。

## ⚠️ 重要提示

### 执行权限

所有脚本在移动后保持了执行权限。如果遇到权限问题，可以运行：

```bash
chmod +x scripts/docker/*.sh
chmod +x scripts/email/*.sh
chmod +x scripts/ci/*.sh
```

### 向后兼容

为了保持向后兼容，可以在根目录创建符号链接：

```bash
# 创建符号链接（可选）
ln -s scripts/docker/start-docker.sh start-docker.sh
ln -s scripts/docker/stop-docker.sh stop-docker.sh
ln -s scripts/docker/check-env.sh check-env.sh
```

但建议直接使用新路径，以保持项目结构的清晰。

## 📖 使用指南

### 快速开始

1. **启动 Docker 环境**
```bash
./scripts/docker/start-docker.sh
```

2. **配置邮件服务**
```bash
./scripts/email/configure-email.sh
```

3. **检查环境配置**
```bash
./scripts/docker/check-env.sh
```

### 常用命令

```bash
# Docker 操作
./scripts/docker/start-docker.sh    # 启动服务
./scripts/docker/stop-docker.sh     # 停止服务
./scripts/docker/check-env.sh       # 检查配置

# 邮件配置
./scripts/email/configure-email.sh  # 配置邮件
./scripts/email/update-smtp-auth-code.sh NEW_CODE  # 更新授权码

# CI/CD 操作
./scripts/ci/check-ci-status.sh     # 检查 CI 状态
./scripts/ci/check-ci.sh            # 运行 CI 检查
./scripts/ci/monitor-ci.sh          # 监控 CI
```

## ✅ 整理效果

### 优点

1. **结构清晰** - 脚本按功能分类，易于查找
2. **易于维护** - 相关脚本集中管理
3. **专业规范** - 符合项目组织最佳实践
4. **便于扩展** - 新脚本可以轻松添加到相应目录
5. **文档完善** - 每个脚本都有详细的使用说明

### 保持不变

以下文件保持在根目录：

- `Makefile` - 构建脚本（提供更高级的命令封装）
- `docker-compose.yml` - Docker 配置
- `test-email.php` - PHP 测试脚本（不是 shell 脚本）
- `composer.phar` - Composer 可执行文件

## 📝 后续维护建议

### 添加新脚本

1. **确定分类**
   - Docker 相关 → `scripts/docker/`
   - 邮件相关 → `scripts/email/`
   - CI/CD 相关 → `scripts/ci/`
   - 其他功能 → 创建新的子目录

2. **命名规范**
   - 使用小写字母和连字符
   - 名称应清晰描述功能
   - 使用 `.sh` 扩展名

3. **添加文档**
   - 在脚本开头添加说明注释
   - 更新 `scripts/README.md`
   - 在根 `README.md` 中添加链接（如果是常用脚本）

### 脚本开发规范

参考 [`scripts/README.md`](../scripts/README.md) 中的"脚本开发规范"部分。

### 测试脚本

在提交前测试脚本：

```bash
# 检查语法
bash -n script-name.sh

# 使用 shellcheck（如果安装）
shellcheck script-name.sh

# 实际运行测试
./script-name.sh --help
```

## 🔍 查找脚本

### 方式一：通过脚本索引

访问 [`scripts/README.md`](../scripts/README.md) 查看完整的脚本列表和使用说明。

### 方式二：通过根 README

根目录的 [`README.md`](../README.md) 包含常用脚本的快速链接。

### 方式三：直接浏览

按功能浏览 `scripts/` 目录下的子目录：
- `docker/` - Docker 相关脚本
- `email/` - 邮件相关脚本
- `ci/` - CI/CD 相关脚本

## 🔄 迁移指南

如果你有自定义脚本或文档引用了旧路径：

### 更新脚本引用

```bash
# 查找所有引用旧路径的文件
grep -r "start-docker.sh" .
grep -r "stop-docker.sh" .
grep -r "check-env.sh" .

# 批量替换（谨慎使用）
find . -type f -name "*.md" -exec sed -i '' 's|./start-docker.sh|./scripts/docker/start-docker.sh|g' {} +
```

### 更新 Git 钩子

如果你的 Git 钩子引用了这些脚本，需要更新路径。

### 更新 CI/CD 配置

如果 CI/CD 配置文件引用了这些脚本，需要更新路径。

## 📞 反馈

如果发现脚本路径问题或有改进建议，请：

1. 提交 Issue
2. 发送邮件至 dev@bujiaban.com
3. 直接提交 Pull Request

## 🎯 相关文档

- [脚本使用指南](../scripts/README.md)
- [Docker 文档](docker/)
- [邮件文档](email/)
- [CI/CD 文档](CI-LOOP-STATUS.md)
- [文档整理说明](DOCUMENTATION_ORGANIZATION.md)

---

**整理完成时间**: 2026-01-21  
**脚本版本**: 1.0  
**兼容性**: 所有脚本保持原有功能，仅路径变更

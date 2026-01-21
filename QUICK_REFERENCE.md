# 快速参考卡片

**项目**: AR 创作平台后端  
**更新日期**: 2026-01-21

## 🚀 快速启动

```bash
# 一键启动 Docker 环境
./scripts/docker/start-docker.sh

# 停止服务
./scripts/docker/stop-docker.sh

# 检查环境
./scripts/docker/check-env.sh
```

## 📖 重要文档

| 文档 | 路径 | 说明 |
|------|------|------|
| 📁 文档中心 | [docs/README.md](docs/README.md) | 所有文档索引 |
| 📜 脚本指南 | [scripts/README.md](scripts/README.md) | 所有脚本说明 |
| 🔒 安全审查 | [docs/security/SECURITY_AUDIT_SUMMARY.md](docs/security/SECURITY_AUDIT_SUMMARY.md) | 安全评估报告 |
| 🐳 Docker 指南 | [docs/docker/DOCKER_QUICK_START.md](docs/docker/DOCKER_QUICK_START.md) | Docker 快速启动 |
| 📧 邮件指南 | [docs/email/邮件功能快速指南.md](docs/email/邮件功能快速指南.md) | 邮件功能说明 |

## 🔧 常用命令

### Docker 操作
```bash
make start          # 启动所有服务
make stop           # 停止服务
make logs           # 查看日志
make shell          # 进入容器
make migrate        # 运行迁移
```

### 脚本操作
```bash
# Docker
./scripts/docker/start-docker.sh
./scripts/docker/stop-docker.sh
./scripts/docker/check-env.sh

# 邮件
./scripts/email/configure-email.sh
./scripts/email/update-smtp-auth-code.sh

# CI/CD
./scripts/ci/check-ci-status.sh
./scripts/ci/check-ci.sh
```

### 测试命令
```bash
# 邮件测试
docker exec -it api7dgamecom-api-1 php yii email-test/all your@email.com

# 运行测试
make test
vendor/bin/codecept run
```

## 🌐 服务端口

| 服务 | 端口 | 访问地址 |
|------|------|----------|
| API 服务 | 8081 | http://localhost:8081 |
| 后台应用 | 8082 | http://localhost:8082 |
| Swagger | 8081 | http://localhost:8081/swagger |
| phpMyAdmin | 8080 | http://localhost:8080 |
| MySQL | 3306 | localhost:3306 |
| Redis | 6379 | localhost:6379 |

## 📂 目录结构

```
项目根目录/
├── docs/              # 📖 文档目录
│   ├── docker/       # Docker 文档
│   ├── email/        # 邮件文档
│   ├── security/     # 安全文档
│   └── sessions/     # 会话记录
├── scripts/           # 📜 脚本目录
│   ├── docker/       # Docker 脚本
│   ├── email/        # 邮件脚本
│   └── ci/           # CI/CD 脚本
├── advanced/          # Yii2 应用
│   ├── api/          # API 应用
│   ├── backend/      # 后台应用
│   ├── common/       # 共享代码
│   └── console/      # 控制台应用
└── .kiro/specs/       # 规范文档
```

## 🔒 安全加固

**当前安全评分**: 5.2/10 ⚠️

**规范文档**: `.kiro/specs/backend-security-hardening/`

**实施阶段**:
- **Phase 1 (P0)**: 立即修复 - 敏感信息、文件上传、速率限制
- **Phase 2 (P1)**: 高优先级 - CORS、JWT、错误处理
- **Phase 3 (P2)**: 中优先级 - 密码历史、审计日志

**开始执行**: 打开 `.kiro/specs/backend-security-hardening/tasks.md`

## 📧 邮件配置

```bash
# 配置邮件服务
./scripts/email/configure-email.sh

# 更新 SMTP 授权码
./scripts/email/update-smtp-auth-code.sh YOUR_AUTH_CODE

# 测试邮件发送
docker exec -it api7dgamecom-api-1 php yii email-test/simple your@email.com
```

## 🔍 故障排查

### Docker 问题
```bash
# 查看日志
docker-compose logs -f api

# 重启服务
docker-compose restart

# 重建容器
docker-compose down
docker-compose up -d --build
```

### 数据库问题
```bash
# 进入数据库容器
docker-compose exec db mysql -u root -p

# 运行迁移
docker-compose exec api php yii migrate
```

### 权限问题
```bash
# 修复权限
docker-compose exec api chmod -R 777 runtime web/assets
```

## 📞 获取帮助

1. **查看文档**: [docs/README.md](docs/README.md)
2. **查看脚本**: [scripts/README.md](scripts/README.md)
3. **提交 Issue**: GitHub Issues
4. **联系团队**: dev@bujiaban.com

## 🔗 快速链接

- [项目 README](README.md)
- [文档中心](docs/README.md)
- [脚本指南](scripts/README.md)
- [安全审查](docs/security/SECURITY_AUDIT_SUMMARY.md)
- [项目整理总结](PROJECT_ORGANIZATION_SUMMARY.md)

---

**提示**: 将此文件加入书签，方便快速查找常用命令和文档！

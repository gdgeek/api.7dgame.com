# 测试目录结构

本目录包含项目的所有测试文件。

## 目录说明

```
tests/
├── manual/              # 手动测试脚本
│   ├── test_health_version.php
│   ├── test_swagger_file.php
│   ├── test_bootstrap.php
│   └── reproduce_swagger.php
│
├── api/                 # API 模块的自动化测试 (Codeception)
│   ├── functional/
│   └── unit/
│
├── backend/             # Backend 模块的自动化测试 (Codeception)
│   ├── functional/
│   └── unit/
│
└── common/              # Common 模块的自动化测试 (Codeception)
    └── unit/
```

## 测试类型

### 1. 手动测试脚本 (`manual/`)
用于快速验证和调试的 PHP 脚本，可以直接运行。

**使用场景:**
- 快速验证某个功能
- 调试问题
- 开发阶段的临时测试

**运行方式:**
```bash
php tests/manual/test_health_version.php
```

### 2. 自动化测试 (Codeception)
使用 Codeception 框架编写的单元测试和功能测试。

**运行方式:**
```bash
# 运行所有测试
php vendor/bin/codecept run

# 运行特定模块
php vendor/bin/codecept run -c api
php vendor/bin/codecept run -c backend
php vendor/bin/codecept run -c common

# 运行特定类型的测试
php vendor/bin/codecept run unit
php vendor/bin/codecept run functional
```

## 最佳实践

1. **手动测试脚本** 应该放在 `manual/` 目录
2. **自动化测试** 应该放在对应模块的 `tests/` 目录
3. 测试文件命名规范:
   - 手动测试: `test_*.php`
   - 单元测试: `*Test.php`
   - 功能测试: `*Cest.php`
4. 每个测试脚本都应该有清晰的注释说明用途和使用方法

## 添加新测试

### 添加手动测试脚本

1. 在 `tests/manual/` 目录创建新文件
2. 添加清晰的注释说明
3. 更新 `tests/manual/README.md`

### 添加自动化测试

```bash
# 生成新的单元测试
php vendor/bin/codecept generate:test unit MyNewTest -c api

# 生成新的功能测试
php vendor/bin/codecept generate:cest functional MyNewCest -c api
```

## 持续集成

在 CI/CD 流程中，建议只运行自动化测试：

```bash
# .gitlab-ci.yml 或 .github/workflows/test.yml
script:
  - composer install
  - php vendor/bin/codecept run
```

手动测试脚本仅用于本地开发和调试。

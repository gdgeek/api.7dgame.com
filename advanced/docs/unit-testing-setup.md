# 单元测试设置文档

## 概述

本文档描述了为项目添加单元测试和 CI 集成的过程。

## 完成的工作

### 1. 测试框架配置

- 使用 PHPUnit 作为测试框架
- 创建了 `phpunit.xml` 配置文件
- 创建了 `test_bootstrap.php` 引导文件，包含：
  - Yii 框架初始化
  - 数据库连接配置
  - JWT 组件配置
  - 路径别名设置

### 2. 测试文件

创建了以下测试文件：

#### `tests/unit/models/UserMethodsTest.php`
- 测试 User 模型的方法签名
- 不需要数据库连接
- 验证以下方法存在：
  - `findByEmail()` - 通过邮箱查找用户
  - `isEmailVerified()` - 检查邮箱是否已验证
  - `markEmailAsVerified()` - 标记邮箱为已验证
- 验证类结构：
  - 实现 `IdentityInterface`
  - 继承自 `ActiveRecord`

#### `tests/unit/models/UserTest.php`
- 测试 User 模型的完整功能
- 需要数据库连接
- 测试用户创建、查找、密码验证等功能

#### `tests/unit/models/UserEmailVerificationTest.php`
- 专门测试邮箱验证功能
- 验证 `email_verified_at` 字段的行为

### 3. CI 配置

更新了 `.github/workflows/ci.yml`：

- 添加了 MySQL 服务容器
- 配置了测试数据库
- 运行数据库迁移
- 执行 PHPUnit 测试
- 只在测试通过后才构建和部署

### 4. 测试策略

采用了两层测试策略：

1. **方法签名测试**（不需要数据库）
   - 快速验证代码结构
   - 可以在没有数据库的环境中运行
   - 验证 API 契约

2. **功能测试**（需要数据库）
   - 完整的集成测试
   - 验证实际的数据库操作
   - 在 CI 环境中运行

## 运行测试

### 本地运行（不需要数据库）

```bash
cd advanced
php vendor/bin/phpunit tests/unit/models/UserMethodsTest.php
```

### 本地运行（需要数据库）

```bash
cd advanced
# 确保数据库配置正确
php vendor/bin/phpunit
```

### CI 环境

测试会在每次推送到任何分支时自动运行。

## 测试覆盖

当前测试覆盖了 User 模型的以下功能：

- ✅ 邮箱验证状态检查
- ✅ 邮箱验证标记
- ✅ 通过邮箱查找用户
- ✅ 用户创建
- ✅ 密码验证
- ✅ 密码强度验证
- ✅ 用户名唯一性验证

## 下一步

1. 添加更多模型的测试
2. 添加控制器测试
3. 添加 API 端点测试
4. 提高测试覆盖率

## 注意事项

- 测试使用独立的测试数据库 `yii2_advanced_test`
- 每个测试后都会清理数据
- CI 环境中的数据库配置在 `.github/workflows/ci.yml` 中
- 本地测试需要配置 `common/config/test.php`

## 故障排除

### 数据库连接错误

如果遇到 "No such file or directory" 错误，检查：
1. MySQL 是否正在运行
2. 数据库配置是否正确
3. 测试数据库是否已创建

### JWT 错误

如果遇到 JWT 相关错误，确保：
1. `bizley/jwt` 包已安装
2. JWT 配置在 `test_bootstrap.php` 中正确设置

### 迁移错误

如果迁移失败，检查：
1. 数据库用户权限
2. 迁移文件是否有语法错误
3. 数据库版本兼容性

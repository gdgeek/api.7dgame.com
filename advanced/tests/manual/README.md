# 手动测试脚本目录

本目录包含用于手动测试和调试的 PHP 脚本。

## 文件说明

### test_health_version.php
测试健康检查和版本号接口

**使用方法:**
```bash
php tests/manual/test_health_version.php
```

**测试内容:**
- `/site/health` - 健康检查接口
- `/site/version` - 版本信息接口

---

### test_swagger_file.php
测试 Swagger API 文档生成

**使用方法:**
```bash
php tests/manual/test_swagger_file.php
```

---

### test_bootstrap.php
测试应用引导程序

**使用方法:**
```bash
php tests/manual/test_bootstrap.php
```

---

### reproduce_swagger.php
重现 Swagger 相关问题的测试脚本

**使用方法:**
```bash
php tests/manual/reproduce_swagger.php
```

---

## 注意事项

1. 这些脚本主要用于开发和调试阶段
2. 不应该在生产环境中运行这些脚本
3. 运行前请确保已安装所有依赖: `composer install`
4. 某些脚本可能需要配置数据库连接

## 自动化测试

如需运行自动化测试，请使用 Codeception:

```bash
# 运行所有测试
php vendor/bin/codecept run

# 运行特定模块的测试
php vendor/bin/codecept run -c api
php vendor/bin/codecept run -c backend
php vendor/bin/codecept run -c common
```

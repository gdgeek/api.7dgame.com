# 腾讯企业邮箱配置指南

## 步骤 1：编辑环境配置文件

打开 `.env.docker` 文件，找到邮件配置部分，修改为：

```bash
# Email Configuration (for password reset, verification)
MAILER_USERNAME=your_email@your_company.com
MAILER_PASSWORD=你的邮箱密码
```

例如：
```bash
MAILER_USERNAME=your_email@your_company.com
MAILER_PASSWORD=your_password_here
```

## 步骤 2：重启 API 服务

配置修改后，需要重启 API 服务使配置生效：

```bash
docker-compose restart api
```

## 步骤 3：测试邮件发送

运行测试脚本发送测试邮件：

```bash
# 发送到你的测试邮箱
docker-compose exec api php test-email.php 你的测试邮箱@example.com
```

例如：
```bash
docker-compose exec api php test-email.php test@example.com
```

## 腾讯企业邮箱 SMTP 配置说明

当前配置：
- **SMTP 服务器**: smtp.exmail.qq.com
- **SMTP 端口**: 25
- **加密方式**: TLS
- **发件人**: your_email@your_company.com

如果端口 25 被封禁，可以尝试：
- 端口 465（SSL）
- 端口 587（TLS）

## 常见问题

### 1. 如果提示"535 Login Fail"
- 检查邮箱地址和密码是否正确
- 确认企业邮箱已开通 SMTP 服务
- 尝试使用授权码而不是密码

### 2. 如果提示连接超时
- 检查网络是否正常
- 尝试更换端口（465 或 587）
- 检查防火墙设置

### 3. 如果需要修改端口
编辑 `files/common/config/main-local.php`，修改：
```php
'port' => '465',  // 或 '587'
'encryption' => 'ssl',  // 如果使用 465 端口
```

## 测试邮件功能的 API 端点

配置完成后，你还可以通过 API 测试：

### 1. 用户注册（会发送验证邮件）
```bash
curl -X POST http://localhost:8081/v1/user/signup \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@example.com",
    "password": "Test123456"
  }'
```

### 2. 请求密码重置（会发送重置邮件）
```bash
curl -X POST http://localhost:8081/v1/user/request-password-reset \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com"
  }'
```

### 3. 重新发送验证邮件
```bash
curl -X POST http://localhost:8081/v1/user/resend-verification-email \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com"
  }'
```

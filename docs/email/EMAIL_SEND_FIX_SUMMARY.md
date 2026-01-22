# 邮件发送问题修复总结

## 问题描述

邮箱验证码接口返回成功，但用户没有收到邮件。

## 根本原因

1. **useFileTransport 设置为 true**：邮件被保存到文件而不是真正发送
2. **发件人地址不匹配**：发件人地址必须与 SMTP 认证用户一致

## 修复步骤

### 1. 修改 useFileTransport 配置

**文件**: `files/common/config/main-local.php`

```php
'mailer' => [
    'class' => \yii\symfonymailer\Mailer::class,
    'viewPath' => '@common/mail',
    'useFileTransport' => false, // 改为 false
    'transport' => [
        'scheme' => 'smtp',
        'host' => 'smtp.exmail.qq.com',
        'username' => getenv('MAILER_USERNAME'),
        'password' => getenv('MAILER_PASSWORD'),
        'port' => 465,
        'encryption' => 'ssl',
    ],
],
```

### 2. 配置发件人地址

**文件**: `files/common/config/params-local.php`

```php
<?php
return [
    'supportEmail' => getenv('MAILER_USERNAME') ?: 'dev@bujiaban.com',
    'adminEmail' => getenv('MAILER_USERNAME') ?: 'dev@bujiaban.com',
];
```

### 3. 修复 EmailService 发件人

**文件**: `advanced/api/modules/v1/services/EmailService.php`

```php
$fromEmail = Yii::$app->params['supportEmail'] ?? getenv('MAILER_USERNAME') ?? 'noreply@example.com';

$result = Yii::$app->mailer->compose(...)
    ->setFrom([$fromEmail => Yii::$app->name . ' 团队'])
    ->setTo($email)
    ->send();
```

### 4. 添加测试接口

**接口**: `GET /v1/email/test`

用于快速测试邮件发送功能，无需认证。

**使用方法**:
```bash
curl -X GET "http://localhost:81/v1/email/test"
```

**成功响应**:
```json
{
  "success": true,
  "message": "测试邮件发送成功",
  "data": {
    "from": "dev@bujiaban.com",
    "to": "nethz@163.com",
    "time": "2026-01-21 22:17:21"
  }
}
```

## 环境变量

确保 Docker 容器中设置了以下环境变量：

```bash
MAILER_USERNAME=dev@bujiaban.com
MAILER_PASSWORD=RGewSjMs9m8V9EP6
```

## 验证步骤

1. **测试邮件发送**:
   ```bash
   curl -X GET "http://localhost:81/v1/email/test"
   ```

2. **检查邮箱**:
   - 查看收件箱
   - 查看垃圾邮件箱
   - 等待 1-2 分钟（可能有延迟）

3. **测试验证码发送**:
   ```bash
   curl -X POST "http://localhost:81/v1/email/send-verification" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"email":"your@email.com"}'
   ```

## 常见问题

### Q1: 仍然收不到邮件？

检查：
1. 垃圾邮件箱
2. SMTP 服务器是否正常
3. 邮箱地址是否正确
4. 查看日志：`docker exec docker-api-1 tail -f /var/www/html/advanced/api/runtime/logs/app.log`

### Q2: 发送失败，提示 "authentication failed"？

检查：
1. MAILER_USERNAME 和 MAILER_PASSWORD 是否正确
2. 密码是否是授权码（不是邮箱密码）
3. SMTP 服务器地址和端口是否正确

### Q3: 提示 "mail from address must be same as authorization user"？

确保发件人地址与 MAILER_USERNAME 一致。

## 文件清单

修改的文件：
- `files/common/config/main-local.php` - 邮件配置
- `files/common/config/params-local.php` - 参数配置（新建）
- `files/api/config/main.php` - 路由配置
- `advanced/api/modules/v1/controllers/EmailController.php` - 添加测试接口
- `advanced/api/modules/v1/services/EmailService.php` - 修复发件人地址

## 测试结果

✅ 测试邮件发送成功  
✅ 发件人地址正确  
✅ SMTP 认证通过  
✅ 邮件已发送到 nethz@163.com

## 下一步

1. 检查邮箱确认收到测试邮件
2. 测试验证码发送功能
3. 如果需要，可以删除测试接口或添加访问限制

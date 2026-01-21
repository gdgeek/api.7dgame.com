# 邮件功能使用指南

本文档详细说明了系统的邮件功能配置、使用方法和测试流程。

## 📋 目录

- [功能概述](#功能概述)
- [配置说明](#配置说明)
- [邮件类型](#邮件类型)
- [测试方法](#测试方法)
- [开发指南](#开发指南)
- [故障排查](#故障排查)

---

## 功能概述

系统使用 **Symfony Mailer 4.0** 作为邮件发送组件，支持以下功能：

- ✅ 邮箱验证码发送
- ✅ 密码重置邮件
- ✅ 邮箱验证链接
- ✅ 自定义邮件模板
- ✅ HTML 和纯文本双格式
- ✅ 腾讯企业邮箱支持

---

## 配置说明

### 1. 环境变量配置

在 `.env.docker` 文件中配置邮件相关环境变量：

```bash
# 邮件配置
MAILER_USERNAME=dev@bujiaban.com
MAILER_PASSWORD=your_smtp_authorization_code
```

**重要提示：**
- `MAILER_PASSWORD` 必须使用 SMTP 授权码，不是邮箱登录密码
- 获取授权码方法请参考 [GET_SMTP_AUTH_CODE.md](GET_SMTP_AUTH_CODE.md)

### 2. 应用配置

邮件配置位于 `files/common/config/main-local.php`：

```php
'mailer' => [
    'class' => \yii\symfonymailer\Mailer::class,
    'viewPath' => '@common/mail',
    'useFileTransport' => false,
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

### 3. 支持的 SMTP 服务器

| 服务商 | SMTP 服务器 | 端口 | 加密方式 |
|--------|-------------|------|----------|
| 腾讯企业邮箱 | smtp.exmail.qq.com | 465 | SSL |
| 腾讯企业邮箱 | smtp.exmail.qq.com | 587 | TLS |
| QQ 邮箱 | smtp.qq.com | 465/587 | SSL/TLS |
| 163 邮箱 | smtp.163.com | 465/994 | SSL |
| Gmail | smtp.gmail.com | 465/587 | SSL/TLS |

---

## 邮件类型

### 1. 验证码邮件

**用途：** 用户注册、登录验证、敏感操作确认

**模板文件：**
- HTML: `advanced/common/mail/verificationCode-html.php`
- 文本: `advanced/common/mail/verificationCode-text.php`

**参数：**
- `code`: 验证码（6位数字）
- `expiryMinutes`: 有效期（分钟）

**示例代码：**
```php
Yii::$app->mailer->compose(
    ['html' => 'verificationCode-html', 'text' => 'verificationCode-text'],
    [
        'code' => '123456',
        'expiryMinutes' => 15,
    ]
)
    ->setFrom(['noreply@bujiaban.com' => 'Bujiaban'])
    ->setTo($userEmail)
    ->setSubject('【Bujiaban】邮箱验证码')
    ->send();
```

### 2. 密码重置邮件

**用途：** 用户忘记密码，请求重置

**模板文件：**
- HTML: `advanced/common/mail/passwordReset-html.php`
- 文本: `advanced/common/mail/passwordReset-text.php`

**参数：**
- `token`: 重置令牌
- `resetUrl`: 重置链接
- `expiryMinutes`: 有效期（分钟）

**示例代码：**
```php
$token = Yii::$app->security->generateRandomString(32);
$resetUrl = 'https://bujiaban.com/reset-password?token=' . $token;

Yii::$app->mailer->compose(
    ['html' => 'passwordReset-html', 'text' => 'passwordReset-text'],
    [
        'token' => $token,
        'resetUrl' => $resetUrl,
        'expiryMinutes' => 60,
    ]
)
    ->setFrom(['noreply@bujiaban.com' => 'Bujiaban'])
    ->setTo($userEmail)
    ->setSubject('【Bujiaban】密码重置请求')
    ->send();
```

### 3. 邮箱验证邮件

**用途：** 新用户注册后验证邮箱

**模板文件：**
- HTML: `advanced/common/mail/emailVerify-html.php`
- 文本: `advanced/common/mail/emailVerify-text.php`

**参数：**
- `user`: 用户对象（需包含 username 和 verification_token）

**示例代码：**
```php
Yii::$app->mailer->compose(
    ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
    ['user' => $user]
)
    ->setFrom(['noreply@bujiaban.com' => 'Bujiaban'])
    ->setTo($user->email)
    ->setSubject('【Bujiaban】邮箱验证')
    ->send();
```

### 4. 简单文本邮件

**用途：** 通知、提醒等简单消息

**示例代码：**
```php
Yii::$app->mailer->compose()
    ->setFrom(['noreply@bujiaban.com' => 'Bujiaban'])
    ->setTo($userEmail)
    ->setSubject('通知标题')
    ->setTextBody('纯文本内容')
    ->setHtmlBody('<h1>HTML内容</h1>')
    ->send();
```

---

## 测试方法

### 方法一：使用测试控制器（推荐）

系统提供了专门的邮件测试控制器，可以快速测试各种邮件功能。

#### 1. 进入 API 容器

```bash
docker exec -it bujiaban-api bash
```

#### 2. 运行测试命令

**测试所有邮件类型：**
```bash
php yii email-test/all your@email.com
```

**测试验证码邮件：**
```bash
php yii email-test/verification-code your@email.com
```

**测试密码重置邮件：**
```bash
php yii email-test/password-reset your@email.com
```

**测试邮箱验证邮件：**
```bash
php yii email-test/email-verify your@email.com
```

**测试简单邮件：**
```bash
php yii email-test/simple your@email.com
```

**查看帮助：**
```bash
php yii email-test
```

#### 3. 测试输出示例

```
========================================
开始测试所有邮件功能
收件人: nethz@163.com
========================================

[1/4] 正在发送简单测试邮件到: nethz@163.com
✓ 简单测试邮件发送成功！

[2/4] 正在发送验证码邮件到: nethz@163.com
✓ 验证码邮件发送成功！
验证码: 123456
有效期: 15 分钟

[3/4] 正在发送密码重置邮件到: nethz@163.com
✓ 密码重置邮件发送成功！
重置链接: https://bujiaban.com/reset-password?token=...
有效期: 60 分钟

[4/4] 正在发送邮箱验证邮件到: nethz@163.com
✓ 邮箱验证邮件发送成功！

========================================
测试结果汇总
========================================
✓ Simple
✓ Verification code
✓ Password reset
✓ Email verify

成功: 4 / 失败: 0
```

### 方法二：使用旧版测试控制器

```bash
docker exec -it bujiaban-api bash
php yii test-email nethz@163.com
```

### 方法三：使用独立测试脚本

```bash
docker exec -it bujiaban-api bash
php /var/www/html/test-email.php
```

---

## 开发指南

### 创建自定义邮件模板

#### 1. 创建模板文件

在 `advanced/common/mail/` 目录下创建两个文件：

**HTML 版本** (`myTemplate-html.php`):
```php
<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $param1 string */
/* @var $param2 int */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>邮件标题</title>
</head>
<body>
    <h1>欢迎 <?= Html::encode($param1) ?></h1>
    <p>您的参数值是: <?= Html::encode($param2) ?></p>
</body>
</html>
```

**纯文本版本** (`myTemplate-text.php`):
```php
<?php
/* @var $this yii\web\View */
/* @var $param1 string */
/* @var $param2 int */
?>
欢迎 <?= $param1 ?>

您的参数值是: <?= $param2 ?>
```

#### 2. 使用模板发送邮件

```php
Yii::$app->mailer->compose(
    ['html' => 'myTemplate-html', 'text' => 'myTemplate-text'],
    [
        'param1' => 'John',
        'param2' => 123,
    ]
)
    ->setFrom(['noreply@bujiaban.com' => 'Bujiaban'])
    ->setTo($userEmail)
    ->setSubject('邮件主题')
    ->send();
```

### 邮件模板最佳实践

1. **始终提供 HTML 和纯文本两个版本**
   - 某些邮件客户端不支持 HTML
   - 纯文本版本作为后备方案

2. **使用响应式设计**
   - 最大宽度设置为 600px
   - 使用内联样式
   - 测试移动端显示效果

3. **安全性**
   - 使用 `Html::encode()` 转义所有用户输入
   - 不要在邮件中包含敏感信息
   - 使用 HTTPS 链接

4. **可访问性**
   - 提供清晰的文字说明
   - 使用语义化的 HTML 标签
   - 确保足够的颜色对比度

### 在控制器中发送邮件

```php
namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;

class UserController extends Controller
{
    public function actionSendVerificationCode()
    {
        $email = Yii::$app->request->post('email');
        
        // 生成验证码
        $code = sprintf('%06d', mt_rand(0, 999999));
        
        // 保存验证码到缓存（15分钟有效）
        Yii::$app->cache->set('verification_code_' . $email, $code, 900);
        
        // 发送邮件
        $result = Yii::$app->mailer->compose(
            ['html' => 'verificationCode-html', 'text' => 'verificationCode-text'],
            [
                'code' => $code,
                'expiryMinutes' => 15,
            ]
        )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setTo($email)
            ->setSubject('【' . Yii::$app->name . '】邮箱验证码')
            ->send();
        
        if ($result) {
            return ['success' => true, 'message' => '验证码已发送'];
        } else {
            return ['success' => false, 'message' => '发送失败'];
        }
    }
}
```

---

## 故障排查

### 问题 1: 邮件发送失败，提示认证错误

**症状：**
```
Authentication failed: 535 Login Fail
```

**解决方案：**
1. 确认使用的是 SMTP 授权码，不是邮箱登录密码
2. 检查 `.env.docker` 中的 `MAILER_PASSWORD` 是否正确
3. 参考 [GET_SMTP_AUTH_CODE.md](GET_SMTP_AUTH_CODE.md) 重新获取授权码
4. 使用 `update-smtp-auth-code.sh` 脚本更新授权码

### 问题 2: 邮件发送成功但收不到

**可能原因：**
1. 邮件被放入垃圾箱
2. 邮件服务器延迟
3. 收件地址错误

**解决方案：**
1. 检查垃圾邮件文件夹
2. 等待 5-10 分钟
3. 验证收件地址是否正确
4. 查看发件箱是否有退信

### 问题 3: 连接超时

**症状：**
```
Connection timeout
```

**解决方案：**
1. 检查网络连接
2. 确认 SMTP 服务器地址和端口正确
3. 检查防火墙设置
4. 尝试使用不同的端口（465 或 587）

### 问题 4: SSL/TLS 证书错误

**症状：**
```
SSL certificate problem
```

**解决方案：**
1. 确认使用正确的加密方式（SSL 或 TLS）
2. 端口 465 使用 SSL
3. 端口 587 使用 TLS
4. 更新系统 CA 证书

### 问题 5: 邮件模板显示异常

**可能原因：**
1. 模板文件路径错误
2. 模板参数缺失
3. HTML 格式问题

**解决方案：**
1. 检查模板文件是否存在于 `advanced/common/mail/` 目录
2. 确认传递了所有必需的参数
3. 验证 HTML 语法是否正确
4. 使用浏览器开发工具检查渲染效果

### 调试技巧

#### 1. 启用文件传输模式（开发环境）

在 `files/common/config/main-local.php` 中：

```php
'mailer' => [
    'class' => \yii\symfonymailer\Mailer::class,
    'viewPath' => '@common/mail',
    'useFileTransport' => true, // 改为 true
    // ... 其他配置
],
```

邮件将保存到 `advanced/api/runtime/mail/` 目录，可以直接查看内容。

#### 2. 查看日志

```bash
# 查看 API 日志
docker exec -it bujiaban-api tail -f /var/www/html/advanced/api/runtime/logs/app.log

# 查看邮件发送日志
docker logs bujiaban-api | grep -i mail
```

#### 3. 测试 SMTP 连接

```bash
docker exec -it bujiaban-api bash

# 安装 telnet
apt-get update && apt-get install -y telnet

# 测试连接
telnet smtp.exmail.qq.com 465
```

---

## 安全建议

1. **不要在代码中硬编码密码**
   - 使用环境变量
   - 不要提交 `.env.docker` 到版本控制

2. **使用授权码而非密码**
   - 授权码可以随时撤销
   - 不会暴露邮箱主密码

3. **限制发送频率**
   - 实现验证码发送间隔限制
   - 防止邮件轰炸

4. **验证邮件地址**
   - 使用正则表达式验证格式
   - 检查域名是否存在

5. **加密敏感链接**
   - 使用 HTTPS
   - 令牌使用加密算法生成
   - 设置合理的过期时间

---

## 相关文档

- [Docker 快速开始](DOCKER_QUICK_START.md)
- [获取 SMTP 授权码](GET_SMTP_AUTH_CODE.md)
- [邮件配置指南](EMAIL_CONFIG_GUIDE.md)
- [Docker 设置完成](DOCKER_SETUP_COMPLETE.md)

---

## 更新日志

### 2026-01-21
- ✅ 升级到 Symfony Mailer 4.0
- ✅ 移除已弃用的 SwiftMailer
- ✅ 创建邮件测试控制器
- ✅ 完善邮件模板
- ✅ 添加完整文档

---

**如有问题，请联系开发团队或查看相关文档。**

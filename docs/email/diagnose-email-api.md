# 邮箱验证 API 400 错误诊断

## 问题分析

`/v1/email/send-verification` 返回 400 错误的可能原因：

### 1. 邮箱未注册（最可能）

**原因**：`SendVerificationForm` 中有 `exist` 验证规则，要求邮箱必须已在数据库中注册。

```php
[
    'email', 
    'exist', 
    'targetClass' => User::class, 
    'targetAttribute' => 'email',
    'message' => '该邮箱未注册'
],
```

**解决方案**：
- 使用数据库中已存在的邮箱进行测试
- 或者修改验证规则（见下文）

### 2. 请求格式错误

**可能的问题**：
- Content-Type 不是 `application/json`
- 请求体格式不正确
- 参数名称错误

**正确的请求格式**：
```bash
curl -X POST http://localhost:81/v1/email/send-verification \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com"}'
```

### 3. 邮箱格式验证失败

**可能的问题**：
- 邮箱格式不正确
- 邮箱为空

## 诊断步骤

### 步骤 1: 检查数据库中是否有测试邮箱

```bash
# 进入 Docker 容器
docker exec -it api.7dgame.com-db-1 mysql -uroot -p

# 查询用户表
USE yii2advanced;
SELECT id, username, email, status FROM user LIMIT 10;
```

### 步骤 2: 使用已存在的邮箱测试

找到一个数据库中存在的邮箱，然后测试：

```bash
curl -X POST http://localhost:81/v1/email/send-verification \
  -H "Content-Type: application/json" \
  -d '{"email":"existing@example.com"}' \
  -v
```

### 步骤 3: 查看详细错误信息

检查 API 日志：

```bash
tail -f advanced/api/runtime/logs/app.log
```

## 解决方案

### 方案 1: 使用已注册邮箱测试（推荐）

这是最简单的方法，使用数据库中已存在的邮箱进行测试。

### 方案 2: 修改验证规则（如果需要支持未注册邮箱）

如果你的业务逻辑需要支持未注册邮箱发送验证码（例如注册流程），需要修改 `SendVerificationForm`：

**文件**: `advanced/api/modules/v1/models/SendVerificationForm.php`

**选项 A**: 完全移除 exist 验证

```php
public function rules()
{
    return [
        ['email', 'required', 'message' => '邮箱地址不能为空'],
        ['email', 'trim'],
        ['email', 'email', 'message' => '邮箱格式不正确'],
        ['email', 'string', 'max' => 255],
        // 移除 exist 验证
    ];
}
```

**选项 B**: 添加场景支持（推荐）

```php
public $scenario = 'default'; // 或 'register'

public function rules()
{
    return [
        ['email', 'required', 'message' => '邮箱地址不能为空'],
        ['email', 'trim'],
        ['email', 'email', 'message' => '邮箱格式不正确'],
        ['email', 'string', 'max' => 255],
        [
            'email', 
            'exist', 
            'targetClass' => User::class, 
            'targetAttribute' => 'email',
            'message' => '该邮箱未注册',
            'on' => 'default' // 只在 default 场景下验证
        ],
    ];
}

public function scenarios()
{
    return [
        'default' => ['email'],  // 验证邮箱必须存在
        'register' => ['email'], // 注册场景，不验证邮箱是否存在
    ];
}
```

### 方案 3: 创建测试用户

在数据库中创建一个测试用户：

```sql
INSERT INTO user (username, email, auth_key, password_hash, status, created_at, updated_at)
VALUES (
    'testuser',
    'test@example.com',
    'test-auth-key',
    '$2y$13$test-password-hash',
    10,
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP()
);
```

## 预期的错误响应格式

如果邮箱未注册，应该返回：

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "请求参数验证失败",
    "details": {
      "email": [
        "该邮箱未注册"
      ]
    }
  }
}
```

## 测试建议

1. **先确认问题**：查看具体的错误响应内容
2. **检查数据库**：确认测试邮箱是否存在
3. **查看日志**：检查 `advanced/api/runtime/logs/app.log`
4. **使用正确的邮箱**：使用数据库中存在的邮箱测试

## 快速测试命令

```bash
# 1. 查看数据库中的邮箱
docker exec -it api.7dgame.com-db-1 mysql -uroot -p123456 -e "USE yii2advanced; SELECT email FROM user LIMIT 5;"

# 2. 使用查询到的邮箱测试 API
curl -X POST http://localhost:81/v1/email/send-verification \
  -H "Content-Type: application/json" \
  -d '{"email":"查询到的邮箱"}' \
  | jq .

# 3. 查看实时日志
tail -f advanced/api/runtime/logs/app.log | grep -A 10 "email/send-verification"
```

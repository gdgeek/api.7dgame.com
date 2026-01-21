# 邮箱验证功能快速参考

## 所有接口一览

| 接口 | 方法 | 路径 | 认证 | 说明 |
|------|------|------|------|------|
| 发送验证码 | POST | `/v1/email/send-verification` | ✓ | 发送 6 位验证码到邮箱 |
| 验证邮箱 | POST | `/v1/email/verify` | ✓ | 验证验证码并绑定邮箱 |
| 查询状态 | GET | `/v1/email/status` | ✓ | 查询当前用户邮箱验证状态 |
| 测试邮件 | GET | `/v1/email/test` | ✗ | 发送测试邮件（仅测试用） |

## 快速开始

### 1. 查询邮箱状态

```bash
curl -X GET "http://localhost:81/v1/email/status" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**响应示例**：
```json
{
  "success": true,
  "data": {
    "email": "user@example.com",
    "email_verified": true,
    "email_verified_at_formatted": "2026-01-21 22:20:00"
  }
}
```

### 2. 发送验证码

```bash
curl -X POST "http://localhost:81/v1/email/send-verification" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"email":"your@email.com"}'
```

### 3. 验证邮箱

```bash
curl -X POST "http://localhost:81/v1/email/verify" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"email":"your@email.com","code":"123456"}'
```

**成功响应**：
```json
{
  "success": true,
  "message": "邮箱验证并绑定成功",
  "data": {
    "user": {
      "email": "your@email.com",
      "email_verified_at": 1737484800
    }
  }
}
```

## 判断邮箱是否已验证

### 方法 1：查询状态接口

```javascript
const response = await axios.get('/v1/email/status');
const isVerified = response.data.data.email_verified;

if (isVerified) {
  console.log('邮箱已验证');
} else {
  console.log('邮箱未验证');
}
```

### 方法 2：检查字段

```javascript
const user = response.data.data;

// 方式 1：检查 email_verified 字段
if (user.email_verified) {
  console.log('已验证');
}

// 方式 2：检查 email_verified_at 字段
if (user.email_verified_at !== null) {
  console.log('已验证，验证时间:', user.email_verified_at_formatted);
}

// 方式 3：检查是否有邮箱且已验证
if (user.email && user.email_verified) {
  console.log('邮箱已绑定并验证:', user.email);
}
```

## 完整验证流程

```javascript
// 1. 检查状态
const status = await checkEmailStatus();

if (!status.data.email_verified) {
  // 2. 发送验证码
  await sendVerificationCode('user@example.com');
  
  // 3. 用户输入验证码
  const code = getUserInput(); // 从表单获取
  
  // 4. 验证邮箱
  const result = await verifyEmail('user@example.com', code);
  
  if (result.success) {
    // 5. 验证成功，更新 UI
    console.log('验证成功！', result.data.user);
  }
}
```

## Vue3 组件示例

```vue
<template>
  <div>
    <!-- 显示验证状态 -->
    <div v-if="emailStatus">
      <span v-if="emailStatus.email_verified" class="verified">
        ✓ 邮箱已验证: {{ emailStatus.email }}
      </span>
      <span v-else class="not-verified">
        ⚠ 邮箱未验证
        <button @click="showVerifyDialog">立即验证</button>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const emailStatus = ref(null);

const checkStatus = async () => {
  const response = await axios.get('/v1/email/status');
  emailStatus.value = response.data.data;
};

onMounted(() => {
  checkStatus();
});
</script>
```

## 常见问题

### Q: 如何知道邮箱是否已验证？

A: 调用 `GET /v1/email/status` 接口，检查返回的 `email_verified` 字段。

### Q: 验证成功后需要重新查询状态吗？

A: 不需要。验证接口 `/v1/email/verify` 成功后会直接返回更新后的用户信息，包含 `email` 和 `email_verified_at` 字段。

### Q: 如何在前端显示验证状态？

A: 参考上面的 Vue3 组件示例，根据 `email_verified` 字段显示不同的 UI。

### Q: 验证码有效期多久？

A: 15 分钟（900 秒）

### Q: 多久可以重新发送验证码？

A: 60 秒

## 数据库字段

用户表 (`user`) 相关字段：

| 字段 | 类型 | 说明 |
|------|------|------|
| email | varchar(255) | 邮箱地址 |
| email_verified_at | int(11) | 验证时间戳（Unix 时间戳） |

**判断逻辑**：
- `email_verified_at IS NULL` → 未验证
- `email_verified_at IS NOT NULL` → 已验证

## 相关文档

- [完整 API 文档](./EMAIL_VERIFICATION_API_FRONTEND.md)
- [状态查询详细说明](./EMAIL_STATUS_API.md)
- [邮件发送问题修复](./EMAIL_SEND_FIX_SUMMARY.md)

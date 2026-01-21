# 邮箱验证状态查询 API

## 接口说明

查询当前登录用户的邮箱验证状态。

## 接口信息

- **接口路径**: `/v1/email/status`
- **请求方法**: `GET`
- **认证要求**: 需要 Bearer Token 认证

## 请求示例

### 使用 curl

```bash
curl -X GET "http://localhost:81/v1/email/status" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Accept: application/json"
```

### 使用 JavaScript (axios)

```javascript
import axios from 'axios';

const checkEmailStatus = async () => {
  try {
    const response = await axios.get('/v1/email/status', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
      }
    });
    return response.data;
  } catch (error) {
    throw error.response.data;
  }
};

// 使用
checkEmailStatus()
  .then(data => {
    console.log('邮箱验证状态:', data.data);
    if (data.data.email_verified) {
      console.log('邮箱已验证');
    } else {
      console.log('邮箱未验证');
    }
  })
  .catch(error => {
    console.error('查询失败:', error);
  });
```

## 成功响应

**HTTP 状态码**: `200 OK`

### 邮箱已验证

```json
{
  "success": true,
  "data": {
    "user_id": 3,
    "username": "testuser",
    "email": "user@example.com",
    "email_verified": true,
    "email_verified_at": 1737484800,
    "email_verified_at_formatted": "2026-01-21 22:20:00"
  }
}
```

### 邮箱未验证

```json
{
  "success": true,
  "data": {
    "user_id": 3,
    "username": "testuser",
    "email": null,
    "email_verified": false,
    "email_verified_at": null,
    "email_verified_at_formatted": null
  }
}
```

## 响应字段说明

| 字段 | 类型 | 说明 |
|------|------|------|
| user_id | integer | 用户 ID |
| username | string | 用户名 |
| email | string\|null | 邮箱地址（未绑定时为 null） |
| email_verified | boolean | 邮箱是否已验证 |
| email_verified_at | integer\|null | 验证时间戳（Unix 时间戳） |
| email_verified_at_formatted | string\|null | 格式化的验证时间 |

## 错误响应

### 未登录 (401 Unauthorized)

```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "用户未登录"
  }
}
```

或

```json
{
  "name": "Unauthorized",
  "message": "Your request was made with invalid credentials.",
  "code": 0,
  "status": 401
}
```

## Vue3 使用示例

### Composable 函数

```javascript
// src/composables/useEmailStatus.js
import { ref } from 'vue';
import axios from 'axios';

export function useEmailStatus() {
  const loading = ref(false);
  const error = ref(null);
  const emailStatus = ref(null);
  
  const checkStatus = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await axios.get('/v1/email/status', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('access_token')}`
        }
      });
      
      emailStatus.value = response.data.data;
      return response.data.data;
    } catch (err) {
      error.value = err.response?.data?.error?.message || '查询失败';
      return null;
    } finally {
      loading.value = false;
    }
  };
  
  return {
    loading,
    error,
    emailStatus,
    checkStatus
  };
}
```

### 组件使用

```vue
<template>
  <div class="email-status">
    <div v-if="loading">加载中...</div>
    
    <div v-else-if="emailStatus">
      <div v-if="emailStatus.email_verified" class="verified">
        <span class="icon">✓</span>
        <span>邮箱已验证</span>
        <p>{{ emailStatus.email }}</p>
        <p class="time">验证时间: {{ emailStatus.email_verified_at_formatted }}</p>
      </div>
      
      <div v-else class="not-verified">
        <span class="icon">⚠</span>
        <span>邮箱未验证</span>
        <button @click="goToVerify">立即验证</button>
      </div>
    </div>
    
    <div v-if="error" class="error">{{ error }}</div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useEmailStatus } from '@/composables/useEmailStatus';

const router = useRouter();
const { loading, error, emailStatus, checkStatus } = useEmailStatus();

onMounted(() => {
  checkStatus();
});

const goToVerify = () => {
  router.push('/verify-email');
};
</script>

<style scoped>
.verified {
  color: #67c23a;
}

.not-verified {
  color: #e6a23c;
}

.error {
  color: #f56c6c;
}

.icon {
  font-size: 24px;
  margin-right: 8px;
}

.time {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}
</style>
```

## 使用场景

1. **用户个人中心**：显示邮箱验证状态
2. **验证提醒**：未验证时显示提示
3. **功能限制**：某些功能要求邮箱已验证
4. **状态同步**：验证成功后刷新状态

## 完整流程示例

```javascript
// 1. 检查邮箱状态
const status = await checkEmailStatus();

if (!status.data.email_verified) {
  // 2. 邮箱未验证，发送验证码
  await sendVerificationCode(email);
  
  // 3. 用户输入验证码
  const code = prompt('请输入验证码');
  
  // 4. 验证邮箱
  const result = await verifyEmail(email, code);
  
  if (result.success) {
    // 5. 验证成功，重新检查状态
    const newStatus = await checkEmailStatus();
    console.log('新状态:', newStatus.data);
  }
}
```

## 注意事项

1. **需要登录**：必须先登录获取 Token
2. **实时状态**：每次调用都会从数据库获取最新状态
3. **缓存建议**：前端可以缓存状态，避免频繁请求
4. **状态更新**：验证成功后应该重新查询状态

## 测试命令

```bash
# 获取 Token（先登录）
TOKEN="your_jwt_token_here"

# 查询邮箱状态
curl -X GET "http://localhost:81/v1/email/status" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  | jq .
```

## 相关接口

- [发送验证码](./EMAIL_VERIFICATION_API_FRONTEND.md#1-发送邮箱验证码)
- [验证邮箱](./EMAIL_VERIFICATION_API_FRONTEND.md#2-验证邮箱验证码)

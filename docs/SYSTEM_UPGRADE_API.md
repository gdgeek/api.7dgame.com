# 系统升级接口说明

## 接口概述

| 项目 | 说明 |
|------|------|
| 路径 | `POST /v1/system/upgrade` |
| 认证 | JWT Bearer Token (需登录用户) |
| 用途 | 数据迁移和版本升级 |

## 功能说明

### 1. 版本管理
- 检查并创建当前版本记录 (Version 表)

### 2. 标签迁移
将 `public`、`checkin` 标签迁移到 Property 系统：
- Tags → Property 表
- verse_tags → verse_property 关联

### 3. Meta 升级
- 为 Meta 生成 UUID (如缺失)
- 刷新资源关联 (`refreshResources`)
- 迁移 Code 数据到 MetaCode (Lua/JS)

### 4. Verse 升级
- 为 Verse 生成 UUID (如缺失)
- 刷新 Meta 关联 (`refreshMetas`)
- 迁移 Code 数据到 VerseCode (Lua/JS)

### 5. 代码迁移详情
将 Code 表数据迁移到 MetaCode/VerseCode：
- `code.lua` → `meta_code.lua` / `verse_code.lua`
- `code.js` → `meta_code.js` / `verse_code.js`

## 响应格式

```json
{
  "meta": {
    "total": 100,
    "success": 98,
    "fail": 2,
    "errors": ["Meta ID 5: error message"]
  },
  "verse": {
    "total": 50,
    "success": 50,
    "fail": 0,
    "errors": []
  }
}
```

## 性能优化

- `set_time_limit(0)` - 无超时限制
- `ignore_user_abort(true)` - 客户端断开后继续执行
- `memory_limit: 512M` - 大数据量处理
- `batch(100)` - 分批处理减少内存占用

## 调用示例

```bash
curl -X POST "https://api.example.com/v1/system/upgrade" \
  -H "Authorization: Bearer <your_jwt_token>"
```

## 注意事项

1. 升级操作幂等，可重复执行
2. 错误不中断流程，记录后继续处理
3. 建议在低峰期执行
4. 执行前建议备份数据库

## 数据库迁移

升级前需要执行数据库迁移：

```bash
cd advanced && php yii migrate
```

### 相关迁移文件
- `m260123_000001_drop_verse_version_meta_version_tables.php` - 删除 verse_version 和 meta_version 表

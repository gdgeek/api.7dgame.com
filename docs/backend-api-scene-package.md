# 场景导出/导入 — 后端 API 实现说明

> 前端已完成全部实现，等待后端提供以下两个接口。

## 1. 导出接口

### `GET /v1/verses/{id}/export`

一次性返回场景的完整数据树，包括 Verse、所有关联 Meta（含 MetaCode）、所有关联 Resource（含文件下载 URL）、以及 Meta-Resource 关联关系。

#### 请求

```
GET /v1/verses/626/export
Authorization: Bearer {token}
```

无请求体。`{id}` 为场景 ID。

#### 响应 `200 OK`

```jsonc
{
  // Verse 主体（与 GET /v1/verses/{id} 返回结构一致）
  "verse": {
    "id": 626,
    "author_id": 1,
    "name": "我的场景",
    "description": "场景描述",
    "info": null,
    "data": { /* Verse JSON 数据，原样返回 */ },
    "version": 3,
    "uuid": "verse-uuid-xxx",
    "editable": true,
    "viewable": true,
    "verseRelease": null,
    "image": { "id": 10, "md5": "...", "type": "image/png", "url": "https://...", "filename": "...", "size": 1024, "key": "..." },
    // ⬇️ 重要：需要包含 verseCode
    "verseCode": {
      "blockly": "<xml>...</xml>",
      "lua": "-- lua code",
      "js": "// js code"
    }
  },

  // 所有关联的 Meta（含 metaCode）
  "metas": [
    {
      "id": 101,
      "author_id": 1,
      "uuid": "meta-uuid-aaa",
      "title": "实体A",
      "data": { /* Meta JSON 数据 */ },
      "events": { "inputs": [], "outputs": [] },
      "image_id": null,
      "image": null,
      "prefab": 0,
      "resources": [],
      "editable": true,
      "viewable": true,
      // ⬇️ 重要：需要包含 metaCode
      "metaCode": {
        "blockly": "<xml>...</xml>",
        "lua": "-- lua code"
      }
    }
  ],

  // 所有关联的 Resource（含 file 下载信息）
  "resources": [
    {
      "id": 201,
      "uuid": "res-uuid-111",
      "name": "模型A",
      "type": "polygen",
      "info": "{\"size\":{\"x\":1,\"y\":2,\"z\":1},\"center\":{\"x\":0,\"y\":1,\"z\":0}}",
      "created_at": "2025-01-15T08:00:00Z",
      // ⬇️ 重要：file 对象必须包含 url（资源文件下载地址）和 md5
      "file": {
        "id": 301,
        "md5": "abc123def456",
        "type": "model/gltf-binary",
        "url": "https://cos.example.com/store/res-uuid-111.glb",
        "filename": "model.glb",
        "size": 524288,
        "key": "res-uuid-111.glb"
      }
    }
  ],

  // Meta 与 Resource 的关联关系
  "metaResourceLinks": [
    { "meta_id": 101, "resource_id": 201 }
  ]
}
```

#### 关键要求

| 字段 | 说明 |
|------|------|
| `verse.verseCode` | 必须包含，前端需要导出脚本代码 |
| `metas[].metaCode` | 必须包含，每个 Meta 的脚本代码 |
| `resources[].file.url` | 必须是可直接下载的 URL，前端会用 axios 下载二进制文件 |
| `resources[].file.md5` | 必须包含，前端用于校验下载文件完整性 |
| `metaResourceLinks` | 使用 `meta_id` 和 `resource_id`（数字 ID），前端会转换为 UUID |

#### 错误响应

| 状态码 | 场景 |
|--------|------|
| `401` | 未登录 |
| `403` | 无权限访问该场景 |
| `404` | 场景不存在 |


---

## 2. 导入接口

### `POST /v1/verses/import`

接收前端组装好的完整场景数据，在服务端一次性创建 Verse、所有 Meta、所有 Resource，并建立关联关系。需要事务保护，任何步骤失败则全部回滚。

#### 请求

```
POST /v1/verses/import
Authorization: Bearer {token}
Content-Type: application/json
```

```jsonc
{
  "verse": {
    "name": "我的场景",
    "description": "场景描述",
    "data": "{\"type\":\"Verse\",\"parameters\":{\"uuid\":\"...\"},\"children\":{\"modules\":[]}}",
    "version": 3,
    "uuid": "verse-uuid-xxx",
    "verseCode": {
      "blockly": "<xml>...</xml>",
      "lua": "-- lua code",
      "js": "// js code"
    }
  },

  "metas": [
    {
      "title": "实体A",
      "data": "{\"type\":\"MetaRoot\",\"parameters\":{\"uuid\":\"...\"},\"children\":{}}",
      "events": "{\"inputs\":[],\"outputs\":[]}",
      "uuid": "meta-uuid-aaa",
      "metaCode": {
        "blockly": "<xml>...</xml>",
        "lua": "-- lua code"
      },
      "resourceFileIds": [501]
    }
  ],

  "resourceFileMappings": [
    {
      "originalUuid": "res-uuid-111",
      "fileId": 501,
      "name": "模型A",
      "type": "polygen",
      "info": "{\"size\":{\"x\":1,\"y\":2,\"z\":1},\"center\":{\"x\":0,\"y\":1,\"z\":0}}"
    }
  ]
}
```

#### 请求字段说明

##### `verse` 对象

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `name` | string | ✅ | 场景名称 |
| `description` | string \| null | ❌ | 场景描述 |
| `data` | string (JSON) | ✅ | Verse 数据，JSON 字符串格式 |
| `version` | number | ✅ | 版本号 |
| `uuid` | string | ✅ | 原始 UUID（用于数据内引用映射） |
| `verseCode` | object \| null | ❌ | 脚本代码 `{ blockly, lua?, js? }` |

##### `metas[]` 数组

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `title` | string | ✅ | 实体名称 |
| `data` | string (JSON) \| null | ❌ | Meta 数据，JSON 字符串格式 |
| `events` | string (JSON) \| null | ❌ | 事件数据，JSON 字符串格式 |
| `uuid` | string | ✅ | 原始 UUID |
| `metaCode` | object \| null | ❌ | 脚本代码 `{ blockly, lua?, js? }` |
| `resourceFileIds` | number[] | ✅ | 该 Meta 关联的已上传文件 ID 列表 |

##### `resourceFileMappings[]` 数组

| 字段 | 类型 | 必填 | 说明 |
|------|------|------|------|
| `originalUuid` | string | ✅ | 资源原始 UUID |
| `fileId` | number | ✅ | 前端上传后获得的新 file_id |
| `name` | string | ✅ | 资源名称 |
| `type` | string | ✅ | 资源类型（`polygen`, `voxel`, `picture`, `video`, `audio`, `particle`） |
| `info` | string | ✅ | 资源元信息 JSON 字符串 |

#### 后端处理流程

```
1. 开启数据库事务

2. 创建 Resource 记录
   - 遍历 resourceFileMappings
   - 为每个 mapping 创建 Resource（name, type, uuid=新生成, file_id=mapping.fileId, info）
   - 记录 originalUuid → 新 resource_id 映射

3. 创建 Verse 记录
   - 使用 verse.name, verse.description, verse.uuid(新生成), verse.version
   - 暂不写入 data（需要等 meta_id 映射完成后替换引用）
   - 记录新 verse_id

4. 创建 VerseCode（如果 verse.verseCode 不为 null）
   - 关联到新 verse_id

5. 创建 Meta 记录
   - 遍历 metas 数组
   - 为每个 meta 创建记录（title, uuid=新生成, verse_id=新verse_id）
   - 暂不写入 data 和 events
   - 记录 原始uuid → 新 meta_id 映射

6. 创建 MetaCode（如果 meta.metaCode 不为 null）
   - 关联到新 meta_id

7. 创建 Meta-Resource 关联
   - 遍历每个 meta 的 resourceFileIds
   - 通过 fileId 找到对应的新 resource_id
   - 创建 meta_resource 关联记录

8. ID 重映射（关键步骤）
   - 解析 verse.data JSON 字符串
   - 将其中所有 meta_id 引用替换为新 meta_id（使用步骤5的映射）
   - 将其中所有 resource 引用替换为新 resource_id（使用步骤2的映射）
   - 更新 Verse 的 data 字段
   
   - 遍历每个 meta，解析 meta.data JSON 字符串
   - 将其中所有 resource 引用替换为新 resource_id
   - 更新 Meta 的 data 和 events 字段

9. 提交事务
```

#### 响应 `200 OK`

```json
{
  "verseId": 700,
  "metaIdMap": {
    "meta-uuid-aaa": 150,
    "meta-uuid-bbb": 151
  },
  "resourceIdMap": {
    "res-uuid-111": 250
  }
}
```

##### 响应字段说明

| 字段 | 类型 | 说明 |
|------|------|------|
| `verseId` | number | 新创建的场景 ID |
| `metaIdMap` | Record<string, number> | 原始 Meta UUID → 新 Meta ID 映射 |
| `resourceIdMap` | Record<string, number> | 原始 Resource UUID → 新 Resource ID 映射 |

#### 错误响应

| 状态码 | 场景 |
|--------|------|
| `400` | 请求数据格式错误或必填字段缺失 |
| `401` | 未登录 |
| `422` | 数据验证失败（如 fileId 不存在） |
| `500` | 服务端创建失败（事务已回滚） |

---

## 3. 数据关系图

```
Verse (1)
  ├── VerseCode (0..1)
  ├── Meta (N)
  │     ├── MetaCode (0..1)
  │     └── MetaResource (M) ──→ Resource
  └── data JSON 中引用 meta_id

Meta
  └── data JSON 中引用 resource_id

Resource
  └── File (1) ── 云存储文件
```

## 4. ID 重映射详解

Verse 和 Meta 的 `data` 字段是 JSON 字符串，内部通过数字 ID 引用其他实体。导入时这些 ID 会变化，需要递归替换。

### Verse data 中的 meta_id 引用

```json
{
  "type": "Verse",
  "children": {
    "modules": [
      {
        "type": "Module",
        "parameters": {
          "meta_id": 101,
          "title": "实体A"
        }
      }
    ]
  }
}
```

替换规则：遍历 JSON 树，找到所有 key 为 `meta_id` 的字段，用 metaIdMap 替换值。

### Meta data 中的 resource 引用

```json
{
  "type": "MetaRoot",
  "children": {
    "entities": [
      {
        "type": "polygen",
        "parameters": {
          "resource": 201,
          "name": "expression"
        }
      }
    ]
  }
}
```

替换规则：遍历 JSON 树，找到所有 key 为 `resource` 且值为数字的字段，用 resourceIdMap 替换值。

---

## 5. 前端已有的资源上传流程

前端在调用导入接口之前，会先通过已有接口上传资源文件：

```
1. POST /v1/upload/file   ← 上传文件二进制数据（FormData）
2. POST /v1/files         ← 创建文件记录，返回 file_id
```

后端导入接口收到的 `resourceFileMappings[].fileId` 就是步骤 2 返回的 `file_id`，可以直接用于创建 Resource 记录。

---

## 6. 注意事项

1. 导入接口必须使用数据库事务，确保原子性
2. 导入时应为所有实体生成新的 UUID（不复用原始 UUID），避免跨账号冲突
3. `verse.data` 和 `metas[].data` 是 JSON 字符串，后端需要解析后做 ID 替换再存储
4. `metas[].events` 也是 JSON 字符串，如果内部有 ID 引用也需要替换
5. 导出接口的 `resources[].file.url` 必须是可公开访问的下载链接（或带签名的临时链接）
6. 建议导入接口增加速率限制，防止滥用

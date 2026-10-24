# API V1 接口文档

## 基础信息

- **Base URL**: `/v1`
- **认证方式**: JWT Bearer Token
- **Content-Type**: `application/json`

---

## 认证相关 (Auth)

### 1. 登录

**端点**: `POST /v1/auth/login`

**请求参数**:

```json
{
  "username": "string (必填)",
  "password": "string (必填)"
}
```

**响应示例**:

```json
{
  "success": true,
  "message": "login",
  "token": {
    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires": "2025-11-24 23:00:00",
    "refreshToken": "random_string_token"
  }
}
```

**错误响应**:

- `400`: username is required
- `400`: password is required
- `400`: no user
- `400`: wrong password

---

### 2. 刷新令牌

**端点**: `POST /v1/auth/refresh`

**请求参数**:

```json
{
  "refreshToken": "string (必填)"
}
```

**响应示例**:

```json
{
  "success": true,
  "message": "refresh",
  "token": {
    "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires": "2025-11-24 23:00:00",
    "refreshToken": "new_random_string_token"
  }
}
```

**错误响应**:

- `400`: refreshToken is required
- `400`: no user
- `400`: save error

---

## 用户管理 (User)

> **认证要求**: 所有用户接口需要 JWT Token

### 3. 获取用户信息

**端点**: `GET /v1/user/info`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**响应示例**:

```json
{
  "success": true,
  "message": "ok",
  "data": {
    "id": 1,
    "userData": {
      "username": "user@example.com",
      "nickname": "User Name"
    },
    "userInfo": {
      "gold": 100,
      "points": 500
    },
    "roles": ["user", "creator"]
  }
}
```

---

### 4. 更新用户数据

**端点**: `POST /v1/user/update`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**请求参数**:

```json
{
  "nickname": "string (可选)",
  "其他用户字段": "..."
}
```

**响应示例**:

```json
{
  "success": true,
  "message": "ok",
  "data": {
    "id": 1,
    "userData": {...},
    "userInfo": {...},
    "roles": [...]
  }
}
```

**错误响应**:

- `400`: 缺少数据
- `400`: {validation errors}

---

### 5. 获取用户创建信息

**端点**: `GET /v1/user/creation`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**响应**: 返回 UserCreation 对象

---

## Apple ID 登录 (Site)

### 6. Apple ID 登录

**端点**: `POST /v1/site/apple-id`

**请求参数**:

```json
{
  "code": "string (Apple授权码)",
  "appleParameter": "object (Apple参数)"
}
```

**响应**: 返回用户信息和 token

---

### 7. 创建 Apple ID 账户

**端点**: `POST /v1/site/apple-id-create`

**请求参数**:

```json
{
  "code": "string",
  "appleParameter": "object",
  "username": "string",
  "password": "string"
}
```

---

### 8. 关联 Apple ID

**端点**: `POST /v1/site/apple-id-link`

**请求参数**:

```json
{
  "code": "string",
  "appleParameter": "object",
  "username": "string",
  "password": "string"
}
```

---

### 9. 登录 (Site)

**端点**: `POST /v1/site/login`

**请求参数**:

```json
{
  "username": "string",
  "password": "string"
}
```

**响应示例**:

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "username": "user@example.com",
  "nickname": "User Name",
  "roles": ["user"]
}
```

---

## 元对象管理 (Meta)

> **认证要求**: JWT Token + 权限控制

### 10. 获取元对象列表

**端点**: `GET /v1/meta/index`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**查询参数**:

- `page`: 页码
- `per-page`: 每页数量

**响应**: 返回 DataProvider 对象，包含分页信息

**响应 Headers**:

```
X-Pagination-Total-Count: 100
X-Pagination-Page-Count: 10
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 10
```

---

### 11. 查看元对象

**端点**: `GET /v1/meta/view?id={id}`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**响应**: 返回单个 Meta 对象

---

### 12. 创建元对象

**端点**: `POST /v1/meta/create`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**请求参数**:

```json
{
  "title": "string",
  "type": "string",
  "events": "array",
  "其他字段": "..."
}
```

**响应**: 返回创建的 Meta 对象或错误信息

---

### 13. 更新元对象

**端点**: `PUT /v1/meta/update?id={id}`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**请求参数**: 同创建

**响应**: 返回更新后的 Meta 对象或错误信息

---

### 14. 删除元对象

**端点**: `DELETE /v1/meta/delete?id={id}`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**错误响应**:

- `403`: You can not delete this item (预制对象不可删除)

---

### 15. 更新元对象代码

**端点**: `POST /v1/meta/update-code?id={id}`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**请求参数**:

```json
{
  "blockly": "string (Blockly XML)",
  "lua": "string (Lua代码)",
  "js": "string (JavaScript代码)"
}
```

**错误响应**:

- `400`: {validation errors}

---

## 场景管理 (Verse)

> **认证要求**: JWT Token + 权限控制

### 16. 获取我的场景列表

**端点**: `GET /v1/verse/index`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**查询参数**:

- `page`: 页码
- `per-page`: 每页数量
- `tags`: 标签 ID 列表 (逗号分隔，如: `1,2,3`)

**响应**: 返回当前用户的场景列表 (DataProvider)

---

### 17. 获取公开场景列表

**端点**: `GET /v1/verses/public`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**查询参数**:

- `page`: 页码
- `per-page`: 每页数量
- `tags`: 标签 ID 列表 (逗号分隔)

**响应**: 返回关联 `property.key=public` 的公开场景列表。

公开状态属于场景属性，分类属于标签，两者独立。`tags` 参数仅匹配分类标签，旧 `tags.public` ID 不再匹配任何场景；混合多个 ID 时，其他分类标签仍按 OR 匹配。标签目录和场景 `expand=tags` 不返回旧 `public` 标签；通过标签新增接口添加它会返回 400。设置公开由 admin 或 root 在属性管理中选择已有 `public` 属性并关联到场景；取消公开则从当前场景移除该属性关联。

所有目录标签都可作为分类使用。标签查询、校验和关联不再依赖 `tags.type`，旧的 `TagsSearch[type]` 参数会被忽略；标签创建与更新只需 `name`、`key`。部署此兼容版本并完成验证后，可删除数据库的 `tags.type` 列，场景公开属性不受影响。

场景列表支持独立的 `public` 查询参数：`1` / `true` 筛选有公开属性的场景，`0` / `false` 筛选没有公开属性的场景；其他值返回 400。`/v1/verses` 仍受当前用户范围限制。`/v1/verses/public?public=0` 返回空列表，不能用该参数扩大公开列表范围。省略 `public` 不增加属性筛选。此查询参数不修改公开状态。

admin 和 root 可通过 `GET /v1/verses/{id}/properties` 查看任意场景属性，通过 `GET /v1/verses/{id}/property-options` 获取属性目录。root 可通过 `POST /v1/verses/{id}/properties` 提交 `{"propertyId":2}` 为任意场景关联已有属性；admin 仅可维护自己或具有协作编辑权限（`editable=true`）的场景，不能跨作者维护不可编辑场景。`DELETE /v1/verses/{id}/properties/{propertyId}` 遵循同一对象权限，只移除当前场景关联，保留全局字典与其他场景关联；重复操作幂等。

仅 root 可向同一 POST 接口提交 `{"key":"checkin","info":"签到场景"}` 创建或复用字典项（key 已存在也要求 root），通过 `GET /v1/verses/{id}/properties/{propertyId}/usage` 预览全局使用量，以及通过 `DELETE /v1/verses/{id}/properties/{propertyId}/global` 删除属性本身和所有场景关联。两种 POST 形式互斥，复用字典项不覆盖已有信息。全局删除 `public` 会使所有关联场景变为私有。

root 还可通过 `GET /v1/verses/global-property-options` 获取无需指定场景的全局筛选目录，通过 `GET /v1/verses/by-property?propertyId=2` 查询跨作者的全局关联场景（含私有场景），并通过 `GET /v1/verses/by-property/{id}?propertyId=2` 查看仍关联该属性的场景详情。普通“我的场景”和详情权限范围保持原样；manager、普通用户和未登录用户不能访问属性明细管理接口，场景派生 `public` 布尔值和公开浏览保留。

旧 `POST`/`DELETE /v1/verse/{id}/public`、`/v1/verses/{id}/public` 及 `add-public`/`remove-public` action 已移除；公开状态写入统一使用属性关联接口。`GET /v1/verses/public` 公开列表保留。完整参数与错误码见 [场景 Property 管理](../../../../docs/SCENE_PROPERTIES_API.md)。

---

### 18. 更新场景代码

**端点**: `POST /v1/verse/update-code?id={id}`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**请求参数**:

```json
{
  "blockly": "string",
  "lua": "string",
  "js": "string"
}
```

**错误响应**:

- `400`: {validation errors}

---

### 19. 标准 RESTful 操作

- `GET /v1/verse/view?id={id}`: 查看场景
- `POST /v1/verse/create`: 创建场景
- `PUT /v1/verse/update?id={id}`: 更新场景
- `DELETE /v1/verse/delete?id={id}`: 删除场景

#### root 跨作者维护场景信息

root 专用“所有场景”入口使用 `GET /v1/verses/all` 和 `GET /v1/verses/all/{id}`，包含全部作者的私有场景。列表可组合 `authorSearch`（用户名/昵称字面子串，去首尾空格，最多 255 字符）、`authorId`、`VerseSearch[name]`、可选 `propertyId`、`tags`、`public`、`sort` 和标准分页；默认 `sort=-id`，`page` 为 1–1000000，`per-page` 为 1–100。响应沿用场景序列化、`expand` 和分页头。详情不依赖当前列表筛选。非 root 直调返回 403，未登录返回 401，非法参数返回 400，场景/筛选属性不存在返回 404，支持 GET/HEAD/OPTIONS。完整约定见 [场景 Property 管理](../../../../docs/SCENE_PROPERTIES_API.md)。

“自己创造”的 `GET /v1/verses` 始终只返回本人创建的场景，协作权限与客户端属性/作者参数都不能扩大此范围。新增读取入口不改变 `editable` / `viewable` 或通用对象检查，不新增 3D、脚本、删除或发布权限。

复用 `PUT /v1/verses/{id}`。作者或已有协作权限的用户继续使用原场景编辑权限。场景 `editable=false` 时，仅 `root` 可以维护下列元信息：

```json
{"name":"场景名称","description":"场景简介","image_id":123,"version":"客户端兼容值"}
```

- `name`、`description`、`image_id` 至少提供一个；`image_id` 是已有文件 ID，允许用 `null` 清除封面。
- `version` 为兼容客户端自动附加的字段，在 root 跨作者元信息更新中忽略，不写入场景内容。
- 空请求、仅含 `version`，或包含 `data`、`info`、`metas`、`author_id`、`uuid`、脚本、公开状态等非白名单字段（包括白名单与保护字段混合）均返回 400，整次不保存。
- 普通用户及非作者管理员没有新增的跨作者权限；无原场景编辑权限且非 root 时返回 403。现有 JWT 认证保留。
- 原作者不变；沿用 `updater_id` 记录本次操作者。元信息更新不会重写场景实体关联，不改变 `editable`，不额外开放 3D 数据、脚本、删除或发布。
- 沿用原有 `ReliableWrite`，可使用配对的 `Idempotency-Key` / `If-Match`；版本冲突返回 409。

分类标签复用 `POST /v1/verses/{id}/tag?tags_id={tagsId}` 和 `DELETE /v1/verses/{id}/tag?tags_id={tagsId}`：root 可维护其他作者场景的分类标签，作者/协作者沿用原权限；其他用户没有新增权限。`public` 仍属于场景属性，不能通过分类标签新增接口设置公开状态。属性读取权限及 root 全局字典管理规则保持不变；属性关联的跨作者写入仅 root，admin 只可维护自己或可协作编辑的场景。

---

## 资源管理 (Resource)

> **认证要求**: JWT Token + 权限控制

### 20. 获取资源列表

**端点**: `GET /v1/resource/index`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**查询参数**:

- `page`: 页码
- `per-page`: 每页数量
- `type`: 资源类型 (可选)

**响应**: 返回当前用户的资源列表

---

### 21. 查看资源

**端点**: `GET /v1/resource/view?id={id}`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**响应**: 返回单个 Resource 对象

---

### 22. 标准 RESTful 操作

- `POST /v1/resource/create`: 创建资源
- `PUT /v1/resource/update?id={id}`: 更新资源
- `DELETE /v1/resource/delete?id={id}`: 删除资源

---

## 文件管理 (File)

> **认证要求**: JWT Token + 权限控制

### 23. 获取文件列表

**端点**: `GET /v1/file/index`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**响应**: 返回当前用户的项目文件列表

---

### 24. 标准 RESTful 操作

- `GET /v1/file/view?id={id}`: 查看文件
- `POST /v1/file/create`: 创建文件
- `PUT /v1/file/update?id={id}`: 更新文件
- `DELETE /v1/file/delete?id={id}`: 删除文件

---

## 快照管理 (Snapshot)

> **认证要求**: JWT Token + 权限控制

### 25. 创建场景快照

**端点**: `POST /v1/snapshot/take-photo?verse_id={verse_id}`

**Headers**:

```
Authorization: Bearer {accessToken}
```

**响应示例**:

```json
{
  "id": 123,
  "uuid": "uuid-string",
  "name": "快照名称",
  "description": "描述",
  "data": {...},
  "code": {...},
  "metas": [...],
  "resources": [...],
  "image": {...}
}
```

**错误响应**:

- `400`: {validation errors}

---

### 26. 标准 RESTful 操作

- `GET /v1/snapshot/index`: 获取快照列表
- `GET /v1/snapshot/view?id={id}`: 查看快照
- `POST /v1/snapshot/create`: 创建快照
- `PUT /v1/snapshot/update?id={id}`: 更新快照
- `DELETE /v1/snapshot/delete?id={id}`: 删除快照

---

## 教育管理 (Education)

> **认证要求**: JWT Token + 权限控制

### 27. 学校信息 (EduSchool)

**端点**:

- `GET /v1/edu-school/index`: 获取学校列表
- `GET /v1/edu-school/view?id={id}`: 查看学校详情
- `POST /v1/edu-school/create`: 创建学校
- `PUT /v1/edu-school/update?id={id}`: 更新学校信息
- `DELETE /v1/edu-school/delete?id={id}`: 删除学校

**Headers**:

```
Authorization: Bearer {accessToken}
```

---

### 28. 教师管理 (EduTeacher)

**端点**:

- `GET /v1/edu-teacher/index`: 获取教师列表
- `GET /v1/edu-teacher/view?id={id}`: 查看教师详情
- `POST /v1/edu-teacher/create`: 创建教师
- `PUT /v1/edu-teacher/update?id={id}`: 更新教师信息
- `DELETE /v1/edu-teacher/delete?id={id}`: 删除教师

**Headers**:

```
Authorization: Bearer {accessToken}
```

---

### 29. 班级管理 (EduClass)

**端点**:

- `GET /v1/edu-class/index`: 获取班级列表
- `GET /v1/edu-class/view?id={id}`: 查看班级详情
- `POST /v1/edu-class/create`: 创建班级
- `PUT /v1/edu-class/update?id={id}`: 更新班级信息
- `DELETE /v1/edu-class/delete?id={id}`: 删除班级

**Headers**:

```
Authorization: Bearer {accessToken}
```

---

### 30. 学生管理 (EduStudent)

**端点**:

- `GET /v1/edu-student/index`: 获取学生列表
- `GET /v1/edu-student/view?id={id}`: 查看学生详情
- `POST /v1/edu-student/create`: 创建学生
- `PUT /v1/edu-student/update?id={id}`: 更新学生信息
- `DELETE /v1/edu-student/delete?id={id}`: 删除学生

**Headers**:

```
Authorization: Bearer {accessToken}
```

---

## 通用说明

### 认证流程

1. 使用 `/v1/auth/login` 或 `/v1/site/login` 获取 token
2. 在后续请求的 Header 中携带: `Authorization: Bearer {accessToken}`
3. Token 过期后使用 `/v1/auth/refresh` 刷新

### 错误响应格式

```json
{
  "name": "Bad Request",
  "message": "错误描述",
  "code": 0,
  "status": 400
}
```

### 分页参数

大多数列表接口支持以下查询参数:

- `page`: 页码 (默认: 1)
- `per-page`: 每页数量 (默认: 20)

### CORS 支持

所有接口均支持 CORS，允许的方法: `GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS`

---

## 其他控制器

项目中还包含以下控制器，但未在此文档中详细列出:

- `DeviceController`: 设备管理
- `PersonController`: 人员管理
- `PhototypeController`: 照片类型管理
- `PrefabController`: 预制件管理
- `SystemController`: 系统管理
- `TagsController`: 标签管理
- `TencentCloudController`: 腾讯云集成
- `TestController`: 测试接口
- `TokenController`: Token 管理
- `ToolsController`: 工具接口
- `UploadController`: 上传管理
- `VerseScriptController`: 场景脚本
- `VerseTagsController`: 场景标签
- `WechatController`: 微信集成

如需这些接口的详细文档，请查看对应的控制器源码。

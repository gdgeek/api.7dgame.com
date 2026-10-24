# 场景 Property 管理

以下接口需要 Bearer JWT。RBAC `admin` 和 `root` 可以查看任意场景的属性与全局属性目录。仅 `root` 可以为没有场景编辑权限的其他作者场景关联或移除属性；`admin` 仅可维护自己或具有协作编辑权限（`editable=true`）的场景属性。仅 `root` 可通过 key 创建/复用全局属性、查看全局使用数量，以及删除全局属性和所有场景关联。`manager`、普通用户及仅为场景作者的用户均不能使用这些属性管理接口。

- `GET /v1/verses/{id}/properties`：返回该场景已关联的 property 数组 `[{"id":1,"key":"public","info":"Public visibility"}]`，按 property ID 升序排列；没有属性返回 `[]`。
- `GET /v1/verses/{id}/property-options`：返回全部全局 property 目录，结构与上方数组一致，按 `key`、ID 升序排列，包含当前场景已关联的属性，供选择和搜索使用；目录为空返回 `[]`。接口会先确认场景存在。
- `POST /v1/verses/{id}/properties`（仅 root）：请求体 `{"key":"checkin","info":"签到场景"}`；返回 `{"success":true,"property":{"id":2,"key":"checkin","info":"签到场景"}}`。
- 同一个 POST 接口也接受 `{"propertyId":2}`（root，或对该场景有编辑权限的 admin），将目录中已有属性关联到场景。响应结构相同。`propertyId` 必须是正整数 JSON 数字，不能是字符串、浮点数或布尔值；不能与 `key` 或 `info` 同时提供（包括 null）。不存在的属性 ID 返回 404。
- `DELETE /v1/verses/{id}/properties/{propertyId}`：root 可操作任意场景，admin 仅可操作 editable 场景；从当前场景移除属性，返回 `{"success":true,"property":{"id":2,"key":"checkin","info":"签到场景"}}`。只删除当前场景与该属性的关联（包括历史重复关联），保留全局 property 字典和其他场景的关联。全局属性存在但当前场景原本未关联时仍返回成功；场景或全局属性不存在时返回 404。支持同路径 `OPTIONS` 预检。

- `GET /v1/verses/{id}/properties/{propertyId}/usage`：返回 `{"property":{"id":2,"key":"checkin","info":"签到场景"},"sceneCount":3}`。数量涵盖所有作者，按不同场景计数，历史重复关联只算一次；当前场景无需已关联该属性。这个数量供删除确认提示使用，实际删除会重新计算。
- `DELETE /v1/verses/{id}/properties/{propertyId}/global`：全局删除属性本身及**所有场景**的关联，返回 `{"success":true,"property":{"id":2,"key":"checkin","info":"签到场景"},"affectedSceneCount":3}`。不删除场景或其他属性；可删除未关联当前场景的属性或无人使用的属性（数量为 0）。场景或全局属性不存在时返回 404（包括重复删除）。以上两条路径均支持 `OPTIONS` 预检。

POST 的 key 形式即使 key 已存在也仅允许 root；admin 必须使用 propertyId 形式，不能通过 key 或 info 创建/改动字典。

root 专用“所有场景”列表使用独立入口。“自己创造”始终只显示当前用户本人创建的场景，选择属性不进入其他作者的场景范围；协作编辑权限也不扩大该列表。

- `GET /v1/verses/all`：列出所有作者的场景，包含私有场景。默认按 ID 降序，复用标准场景数组、分页响应头及 `expand=author,image,public`。可组合 `VerseSearch[name]` 场景名称、`authorSearch` 用户名或昵称字面子串、`authorId` 精确作者 ID、`propertyId` 属性、`tags` 分类、`public` 公开状态、`sort`、`page` 和 `per-page`。属性历史重复关联不会造成重复场景。`authorSearch` 去除首尾空格后匹配，空串不增加条件；`%` 和 `_` 按字面字符处理。
- `GET /v1/verses/all/{id}`：返回任意作者场景详情（包含私有场景），支持原有 `expand`，不受当前列表的作者、属性或名称筛选影响；场景不存在返回 404。

以上两条仅 root + JWT 可用：已登录的 admin/普通用户直调返回 403，未登录返回 401，支持 GET/HEAD 和 OPTIONS 预检。列表 `authorSearch`、`VerseSearch[name]` 最多 255 个 UTF-8 字符；`authorId`、`propertyId`、详情 ID 必须是正整数且不超出服务器整数范围；`page` 范围 1–1000000，`per-page` 范围 1–100；`tags` 最多 100 个逗号分隔的正整数 ID。参数类型、编码或范围无效返回 400，属性不存在返回 404。未知作者 ID 或没有匹配结果返回空列表。其他搜索字段和排序保留既有 `VerseSearch` / Yii 白名单行为。

此读取入口保留原来的 `editable` / `viewable` 值，不授予 3D、脚本、删除或发布权限。root 维护他人元信息仍走受限的 `PUT /v1/verses/{id}`，仅允许 `name` / `description` / `image_id`；属性关联使用本文已有接口，分类使用原 tag 接口。

以下全局属性筛选兼容入口保留：

- `GET /v1/verses/global-property-options`：无需场景 ID，返回完整 `SceneProperty[]`，按 key、ID 排序。
- `GET /v1/verses/by-property?propertyId=2`：返回关联该属性的**全局场景**（包括其他作者的私有场景），使用 EXISTS 关联过滤，历史重复关联只返回一个场景。支持现有 `VerseSearch[...]`、`sort`、`page`、`per-page`、`expand` 和 `tags`/`public` 过滤；响应数组及分页头与现有场景列表一致。属性存在但无关联时返回 `[]`。
- `GET /v1/verses/by-property/{id}?propertyId=2`：查看全局属性结果中的一个场景，支持 `expand`；场景必须仍绑定属性，场景不存在或未绑定返回 404。该显式 root 只读入口允许查看其他作者的私有场景。

以上三条仅 root + JWT 可用，admin/manager/user 返回 403，未登录返回 401；支持 OPTIONS。`propertyId` 缺失、非正整数或超出整数范围返回 400，属性不存在返回 404。普通 `GET /v1/verses` 仍仅返回当前用户自己的场景，普通场景详情仍遵循原有 `viewable` 限制。

`key` 必须是字符串，去除首尾空白后为 1–255 个 UTF-8 字符。`info` 可省略或为 null，也可为最多 65535 字节的 UTF-8 字符串。读取兼容已有 JSON info。

property 是全局字典：已有 key 会复用其 ID 和 info，新请求不会修改其他场景使用的全局 info。按 ID 选择已有属性也不修改字典。同一场景通过 key 或 ID 重复添加相同属性都成功且不会重复关联；新增、移除统一先锁定 MySQL 全局属性行，再锁场景行；全局删除持有同一属性锁，事务内清理全部关联和字典，防止并发按 ID 添加产生已删除属性的关联。关联存在判断和实际删除数量使用当前锁定读取，避免 MySQL 可重复读旧快照；唯一索引/主键竞争引发 1205 或 1213 时整个短事务最多尝试 3 次，不重试调用方的外层事务。删除后按旧 ID 添加返回 404；之后显式用同一 key 新建会产生新字典记录，可再次关联。场景不存在返回 404；字段无效或混用两种请求形式返回 400；未认证返回 401；不满足上述操作角色要求返回 403。

`public` 仍通过 `property.key=public` 控制公开状态；新建或选择该 property 都会使场景公开，从场景移除该 property 会取消公开。旧的 POST/DELETE `/v1/verse/{id}/public`、`/v1/verses/{id}/public` 及 action 入口已移除（返回 404）；公开/取消公开统一通过属性 ID 关联/移除 `public` 完成：root 可操作任意场景，admin 仅可操作自己或可协作编辑的场景。admin 无法重建已删除的 `public` 字典项，须由 root 使用 key 形式创建。`GET /v1/verses/public` 公开场景列表和分类标签流程保留。全局删除 `public` 会使全部关联场景变为私有，界面应显示该影响和关联场景数，并让 root 明确确认。此功能不提供修改全局 property 字典的接口。

属性明细只由上述专用接口提供。普通场景 `fields`/`expand` 白名单不包含 `properties`、`verseProperties`，即使低权限用户显式请求这些关联及其嵌套字段，也不会返回属性字典或关联明细；派生 `public` 布尔值和公开场景列表继续可用。主后端没有可绕过此权限矩阵的通用 property/verse-property CRUD 控制器或路由。

# 场景 Property 管理

这两个接口需要 Bearer JWT，且仅 RBAC `root` 角色可访问。root 可以操作任意场景，包括其他作者的场景；普通用户、作者和 admin 均不能通过这两个接口查看或新增 property。

- `GET /v1/verses/{id}/properties`：返回该场景已关联的 property 数组 `[{"id":1,"key":"public","info":"Public visibility"}]`，按 property ID 升序排列；没有属性返回 `[]`。
- `POST /v1/verses/{id}/properties`：请求体 `{"key":"checkin","info":"签到场景"}`；返回 `{"success":true,"property":{"id":2,"key":"checkin","info":"签到场景"}}`。

`key` 必须是字符串，去除首尾空白后为 1–255 个 UTF-8 字符。`info` 可省略或为 null，也可为最多 65535 字节的 UTF-8 字符串。读取兼容已有 JSON info。

property 是全局字典：已有 key 会复用其 ID 和 info，新请求不会修改其他场景使用的全局 info。同一场景重复添加同一 key 成功且不会重复关联。场景不存在返回 404；字段无效返回 400；未认证返回 401；非 root 返回 403。

`public` 仍通过 `property.key=public` 控制公开状态；新增该 property 会使场景公开。现有公开/取消公开接口及分类标签接口保持原有权限与行为。此功能不提供修改、删除 property 或全局 property 列表。

---
inclusion: auto
---

# 文件上传架构

本平台所有文件上传都通过腾讯云 COS（对象存储）完成，不存在上传到服务器本地的场景。

## 流程
1. 前端通过 `/v1/tencent-clouds/token` 获取 COS 临时密钥
2. 前端直传文件到 COS
3. 前端调用 `POST /v1/files` 创建 File 数据库记录（传 url/filename/key）

## 注意
- `FileController` 的 `create` action 必须保留，用于创建 File 记录
- 不要实现任何服务器端文件接收/存储逻辑
- File 模型的 `url` 字段指向 COS 地址

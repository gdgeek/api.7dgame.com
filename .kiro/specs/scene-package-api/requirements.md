# 需求文档：场景包导出/导入 API

## 简介

本功能为现有的场景（Verse）系统提供两个 REST API 端点，支持将完整场景数据树导出和导入。每个端点同时支持两种格式：JSON 直接交互和 ZIP 压缩包。导出接口返回 Verse、所有关联 Meta（含 MetaCode）、所有关联 Resource（含 File 下载 URL）以及 Meta-Resource 关联关系。导入接口接收场景数据，在数据库事务保护下创建所有实体、生成新 UUID、完成 ID 重映射，并返回新旧 ID 映射表。

## 术语表

- **Verse**：场景模型，包含场景名称、描述、JSON 数据结构（data 字段）、版本号等
- **VerseCode**：场景脚本代码模型，包含 blockly、lua、js 三种格式，与 Verse 一对一关联
- **Meta**：实体模型，场景中的组成元素，包含 JSON 数据（data 字段）、事件配置（events 字段）等
- **MetaCode**：实体脚本代码模型，包含 blockly、lua 等格式，与 Meta 一对一关联
- **Resource**：资源模型，包含 3D 模型、图片、视频、音频等文件资源，通过 file_id 关联 File 记录
- **MetaResource**：Meta 与 Resource 的多对多关联记录（联结表）
- **VerseMeta**：Verse 与 Meta 的多对多关联记录（联结表）
- **File**：文件模型，存储云端文件的元信息（md5、url、filename、size、key、type）
- **Export_API**：场景导出接口，`GET /v1/verses/{id}/export`
- **Import_API**：场景导入接口，`POST /v1/verses/import`
- **ID_Remapper**：ID 重映射组件，负责递归遍历 JSON 数据树，将旧实体 ID 替换为新创建的实体 ID
- **ScenePackageService**：场景包服务层，封装导出和导入的核心业务逻辑
- **UuidHelper**：UUID 生成工具，用于为新创建的实体生成 UUID v4
- **Scene_Data_Tree**：场景数据树，包含 verse、metas、resources、metaResourceLinks 四个顶层字段的完整场景数据结构

## 需求

### 需求 1：场景数据导出（JSON 格式）

**用户故事：** 作为场景创作者，我希望通过 API 获取完整的场景数据树（JSON 格式），以便前端可以处理和展示场景数据。

#### 验收标准

1. WHEN 已认证用户请求导出有权访问的场景且 Accept 头为 `application/json` 或未指定格式, THE Export_API SHALL 返回 200 状态码和 JSON 格式的 Scene_Data_Tree
2. WHEN 构建 Scene_Data_Tree 中的 verse 部分, THE ScenePackageService SHALL 包含 verse 的基本字段（id、author_id、name、description、info、data、version、uuid）以及关联的 verseCode（blockly、lua、js）和 image 对象
3. WHEN 构建 Scene_Data_Tree 中的 metas 部分, THE ScenePackageService SHALL 为每个关联的 Meta 包含基本字段（id、author_id、uuid、title、data、events、image_id、prefab）以及关联的 metaCode（blockly、lua）和 resources 数组
4. WHEN 构建 Scene_Data_Tree 中的 resources 部分, THE ScenePackageService SHALL 为每个关联的 Resource 包含基本字段（id、uuid、name、type、info、created_at）以及关联的 file 对象（id、md5、type、url、filename、size、key）
5. WHEN 构建 Scene_Data_Tree 中的 metaResourceLinks 部分, THE ScenePackageService SHALL 以数组形式包含所有 meta_id 与 resource_id 的对应关系
6. WHEN 导出数据中包含 resource 的 file 对象, THE ScenePackageService SHALL 确保 file.url 为可直接下载的资源文件地址

### 需求 2：场景数据导出（ZIP 格式）

**用户故事：** 作为场景创作者，我希望将完整场景导出为 ZIP 压缩包下载，以便离线备份或通过文件方式分享给其他用户。

#### 验收标准

1. WHEN 已认证用户请求导出场景且 Accept 头为 `application/zip`, THE Export_API SHALL 返回 ZIP 格式的文件流响应，Content-Type 为 `application/zip`
2. WHEN 生成 ZIP 导出文件, THE ScenePackageService SHALL 将 Scene_Data_Tree 序列化为 JSON 并存储为 ZIP 内的 `scene.json` 文件
3. WHEN 返回 ZIP 文件, THE Export_API SHALL 设置 Content-Disposition 头为 `attachment`，文件名包含场景名称

### 需求 3：导出权限控制

**用户故事：** 作为系统管理者，我希望导出接口有适当的权限控制，以确保用户只能导出有权访问的场景。

#### 验收标准

1. WHEN 未认证用户请求导出场景, THE Export_API SHALL 返回 401 状态码
2. WHEN 已认证用户请求导出不存在的场景, THE Export_API SHALL 返回 404 状态码
3. WHEN 已认证用户请求导出无权访问的场景（viewable 为 false）, THE Export_API SHALL 返回 403 状态码

### 需求 4：场景数据导入（JSON 格式）

**用户故事：** 作为场景创作者，我希望通过 JSON 请求体提交场景数据即可将完整场景导入到我的账号中，资源文件已预先上传。

#### 验收标准

1. WHEN 已认证用户提交 Content-Type 为 `application/json` 的有效场景导入数据, THE Import_API SHALL 在数据库事务中创建新的 Verse 记录（使用请求中的 name、description、version 字段）
2. WHEN 导入数据包含 verseCode 对象, THE Import_API SHALL 为新创建的 Verse 创建关联的 VerseCode 记录（blockly、lua、js）
3. WHEN 导入数据包含 metas 数组, THE Import_API SHALL 为每个 meta 创建新的 Meta 记录（使用 title 字段）并建立与新 Verse 的 VerseMeta 关联
4. WHEN 导入数据中 meta 包含 metaCode 对象, THE Import_API SHALL 为对应的新 Meta 创建关联的 MetaCode 记录
5. WHEN 导入数据包含 resourceFileMappings 数组, THE Import_API SHALL 为每个 mapping 创建新的 Resource 记录（使用 name、type、info 字段，file_id 取自 mapping.fileId）
6. WHEN 导入数据中 meta 包含 resourceFileIds 数组, THE Import_API SHALL 通过 fileId 找到对应的新 Resource 并创建 MetaResource 关联记录
7. WHEN 导入成功, THE Import_API SHALL 返回 200 状态码和包含 verseId、metaIdMap（原始 UUID 到新 Meta ID 的映射）、resourceIdMap（原始 UUID 到新 Resource ID 的映射）的 JSON 响应

### 需求 5：场景数据导入（ZIP 格式）

**用户故事：** 作为场景创作者，我希望上传场景 ZIP 包即可将场景导入到我的账号中，无需手动拆解数据。

#### 验收标准

1. WHEN 已认证用户提交 Content-Type 为 `multipart/form-data` 的 ZIP 文件, THE Import_API SHALL 解压 ZIP 并从 `scene.json` 中解析场景数据
2. WHEN ZIP 文件解析成功, THE Import_API SHALL 使用与 JSON 导入相同的业务逻辑处理场景数据（创建实体、ID 重映射等）
3. WHEN ZIP 文件不包含 `scene.json`, THE Import_API SHALL 返回 400 状态码和描述性错误信息
4. WHEN 上传的文件不是有效的 ZIP 格式, THE Import_API SHALL 返回 400 状态码和描述性错误信息

### 需求 6：导入 ID 重映射

**用户故事：** 作为场景创作者，我希望导入后的场景数据中所有内部 ID 引用都指向正确的新实体，以确保导入的场景能正常运行。

#### 验收标准

1. WHEN 导入过程中处理 verse.data JSON 字符串, THE ID_Remapper SHALL 递归遍历 JSON 树，将所有 key 为 `meta_id` 的字段值替换为对应的新 Meta ID
2. WHEN 导入过程中处理 verse.data JSON 字符串, THE ID_Remapper SHALL 递归遍历 JSON 树，将所有 key 为 `resource` 且值为数字类型的字段值替换为对应的新 Resource ID
3. WHEN 导入过程中处理每个 meta.data JSON 字符串, THE ID_Remapper SHALL 递归遍历 JSON 树，将所有 key 为 `resource` 且值为数字类型的字段值替换为对应的新 Resource ID
4. WHEN 导入过程中处理每个 meta.events JSON 字符串, IF events 内部包含 ID 引用, THEN THE ID_Remapper SHALL 对 events 数据执行与 meta.data 相同的 resource ID 替换逻辑
5. FOR ALL 有效的 ID 映射表和包含 ID 引用的 JSON 数据, THE ID_Remapper SHALL 保证重映射后的 JSON 数据中所有被替换的 ID 值均存在于对应的映射表中（重映射完整性）

### 需求 7：导入 UUID 生成

**用户故事：** 作为系统管理者，我希望导入时为所有实体生成全新的 UUID，以避免跨账号或重复导入时的 UUID 冲突。

#### 验收标准

1. WHEN 创建导入的 Verse 记录, THE Import_API SHALL 使用 UuidHelper 为 Verse 生成新的 UUID v4 而非复用请求中的原始 UUID
2. WHEN 创建导入的 Meta 记录, THE Import_API SHALL 使用 UuidHelper 为每个 Meta 生成新的 UUID v4 而非复用请求中的原始 UUID
3. WHEN 创建导入的 Resource 记录, THE Import_API SHALL 使用 UuidHelper 为每个 Resource 生成新的 UUID v4 而非复用请求中的原始 UUID

### 需求 8：导入事务安全

**用户故事：** 作为场景创作者，我希望导入操作是原子性的，以确保不会因部分失败而产生不完整的数据。

#### 验收标准

1. THE Import_API SHALL 在单个数据库事务中执行所有创建和更新操作
2. IF 导入过程中任何步骤失败（模型验证失败、数据库写入失败等）, THEN THE Import_API SHALL 回滚所有已执行的数据库操作
3. WHEN 事务回滚发生, THE Import_API SHALL 返回 500 状态码和包含错误描述的 JSON 响应

### 需求 9：导入数据验证

**用户故事：** 作为系统管理者，我希望导入接口对输入数据进行严格验证，以防止无效数据进入系统。

#### 验收标准

1. WHEN 请求体缺少 verse 对象, THE Import_API SHALL 返回 400 状态码
2. WHEN verse 对象缺少必填字段（name、data、version、uuid）, THE Import_API SHALL 返回 400 状态码和具体的缺失字段信息
3. WHEN metas 数组中的元素缺少必填字段（title、uuid）, THE Import_API SHALL 返回 400 状态码和具体的缺失字段信息
4. WHEN resourceFileMappings 数组中的元素缺少必填字段（originalUuid、fileId、name、type、info）, THE Import_API SHALL 返回 400 状态码和具体的缺失字段信息
5. WHEN resourceFileMappings 中的 fileId 引用不存在的 File 记录, THE Import_API SHALL 返回 422 状态码和具体的错误信息
6. WHEN 未认证用户请求导入场景, THE Import_API SHALL 返回 401 状态码

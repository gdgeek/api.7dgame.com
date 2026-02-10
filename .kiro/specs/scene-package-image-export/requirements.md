# 需求文档

## 简介

增强场景包导出/导入功能，使其在导入时能够根据导出数据中的 Verse 图片和 Meta 图片信息，自动创建 File 数据库记录并关联到对应的 Verse 和 Meta 模型。导出端已包含图片文件信息，无需修改。导入端需要新增从导出数据重建 File 记录的逻辑。不涉及实际文件的下载或上传，仅创建指向相同 COS URL 的数据库记录。

## 术语表

- **ScenePackageService**: 负责场景包导出和导入核心逻辑的服务类
- **ScenePackageController**: 处理场景包 HTTP 请求的控制器，负责参数验证和调用 Service
- **File**: 文件模型，存储文件元数据（url、md5、type、filename、size、key），对应 `file` 数据库表
- **Verse**: 场景模型，包含 `image_id` 字段（外键关联 File 表）
- **Meta**: 元数据模型，包含 `image_id` 字段（外键关联 File 表）
- **image_data**: 导出 JSON 中的图片文件信息对象，包含 url、md5、type、filename、size、key 字段
- **JSON 导入**: 通过 `POST /v1/scene-package/verses/import` 端点的 JSON 格式导入
- **ZIP 导入**: 通过 `POST /v1/scene-package/verses/import-zip` 端点的 ZIP 格式导入

## 需求

### 需求 1：导入时重建 Verse 图片 File 记录

**用户故事：** 作为开发者，我希望在导入场景包时自动重建 Verse 的图片 File 记录，以便导入后的 Verse 能正确关联其预览图片。

#### 验收标准

1. WHEN 导入数据中 `verse.image` 包含有效的图片信息（url、filename、key 均存在），THE ScenePackageService SHALL 创建一条新的 File 记录，并将新 File 的 id 设置为导入 Verse 的 `image_id`
2. WHEN 导入数据中 `verse.image` 为 null 或不存在，THE ScenePackageService SHALL 将导入 Verse 的 `image_id` 保持为 null
3. WHEN 创建 Verse 图片 File 记录失败，THE ScenePackageService SHALL 回滚整个导入事务并抛出异常

### 需求 2：导入时重建 Meta 图片 File 记录

**用户故事：** 作为开发者，我希望在导入场景包时自动重建每个 Meta 的图片 File 记录，以便导入后的 Meta 能正确关联其预览图片。

#### 验收标准

1. WHEN 导入数据中某个 `meta.image` 包含有效的图片信息（url、filename、key 均存在），THE ScenePackageService SHALL 创建一条新的 File 记录，并将新 File 的 id 设置为该导入 Meta 的 `image_id`
2. WHEN 导入数据中某个 `meta.image` 为 null 或不存在，THE ScenePackageService SHALL 将该导入 Meta 的 `image_id` 保持为 null
3. WHEN 创建 Meta 图片 File 记录失败，THE ScenePackageService SHALL 回滚整个导入事务并抛出异常

### 需求 3：File 记录创建规则

**用户故事：** 作为开发者，我希望从导出数据重建的 File 记录包含正确的字段值，以便文件信息的完整性得到保证。

#### 验收标准

1. THE ScenePackageService SHALL 使用导出数据中的 url、filename、key 作为新 File 记录的必填字段
2. THE ScenePackageService SHALL 使用导出数据中的 md5、type、size 作为新 File 记录的可选字段（存在则设置）
3. THE ScenePackageService SHALL 将新 File 记录的 `user_id` 设置为当前登录用户的 ID（由 File 模型的 beforeValidate 自动处理）
4. THE ScenePackageService SHALL 为每次导入创建全新的 File 记录，不复用已有的 File ID

### 需求 4：导入数据验证

**用户故事：** 作为开发者，我希望导入时对图片数据进行合理验证，以便在数据不完整时能及时发现问题。

#### 验收标准

1. WHEN 导入数据中 `verse.image` 存在但缺少必填字段（url、filename、key 中任一），THEN THE ScenePackageController SHALL 返回 400 错误并指明缺失的字段
2. WHEN 导入数据中某个 `meta.image` 存在但缺少必填字段（url、filename、key 中任一），THEN THE ScenePackageController SHALL 返回 400 错误并指明缺失的字段
3. WHEN 导入数据中不包含 `verse.image` 或 `meta.image` 字段，THE ScenePackageController SHALL 正常处理导入（图片数据为可选）

### 需求 5：事务一致性

**用户故事：** 作为开发者，我希望图片 File 记录的创建在现有导入事务内完成，以便导入操作的原子性得到保证。

#### 验收标准

1. THE ScenePackageService SHALL 在现有的数据库事务内创建图片 File 记录
2. IF 导入过程中任何步骤失败（包括 File 记录创建），THEN THE ScenePackageService SHALL 回滚所有已创建的记录（包括 File、Verse、Meta 等）

### 需求 6：JSON 和 ZIP 导入一致性

**用户故事：** 作为开发者，我希望 JSON 导入和 ZIP 导入对图片数据的处理逻辑一致，以便两种导入方式产生相同的结果。

#### 验收标准

1. THE ScenePackageService SHALL 对 JSON 导入和 ZIP 导入使用相同的图片 File 记录创建逻辑
2. WHEN 通过 ZIP 导入时，THE ScenePackageController SHALL 从 scene.json 中提取图片数据并传递给 ScenePackageService（无需在 ZIP 中打包实际图片文件）

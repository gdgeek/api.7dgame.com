# Implementation Plan: 场景包导出/导入 API

## Overview

采用方案 B（Service 层 + 独立 Controller）实现场景导出/导入功能。按照依赖顺序：先实现无状态工具类（IdRemapper），再实现核心服务层（ScenePackageService），然后实现 Controller 和路由配置，最后集成测试。

## Tasks

- [x] 1. 创建 IdRemapper 工具类
  - [x] 1.1 创建 `advanced/api/modules/v1/helpers/IdRemapper.php`
    - 实现 `remap(mixed $data, array $replacements): mixed` 静态方法
    - 递归遍历 JSON 数据树（数组），匹配 key 名和值类型后替换
    - 支持 `numericOnly` 选项（resource 键仅替换数字值）
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x]* 1.2 编写 IdRemapper 属性测试
    - 创建 `advanced/tests/unit/helpers/IdRemapperPropertyTest.php`
    - **Property 4: ID 重映射替换正确性**
    - **Property 5: ID 重映射完整性不变量**
    - **Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5**

  - [x]* 1.3 编写 IdRemapper 单元测试
    - 创建 `advanced/tests/unit/helpers/IdRemapperTest.php`
    - 测试 verse.data 中 meta_id 替换
    - 测试 meta.data 中 resource（数字值）替换
    - 测试嵌套 JSON 结构的递归替换
    - 测试 numericOnly 不替换字符串类型的 resource 值
    - 测试空数据和空映射表的边界情况
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [x] 2. 创建 ScenePackageService 服务层
  - [x] 2.1 创建 `advanced/api/modules/v1/services/ScenePackageService.php`
    - 实现 `buildExportData(int $verseId): array` 方法
    - 查询 Verse 及关联的 VerseCode、Image
    - 查询所有关联 Metas（通过 VerseMeta）及各 Meta 的 MetaCode、Resources
    - 查询所有关联 Resources 及 File 对象
    - 查询 MetaResource 关联关系构建 metaResourceLinks
    - 组装并返回 Scene_Data_Tree 结构
    - _Requirements: 1.2, 1.3, 1.4, 1.5, 1.6_

  - [x] 2.2 在 ScenePackageService 中实现 `importScene(array $data): array` 方法
    - 开启数据库事务
    - 按顺序创建 Resources（新 UUID，file_id 取自 resourceFileMappings）
    - 创建 Verse（新 UUID，data 暂空）
    - 创建 VerseCode（如有）
    - 创建 Metas（新 UUID，data/events 暂空）并建立 VerseMeta 关联
    - 创建 MetaCodes（如有）
    - 创建 MetaResource 关联（通过 resourceFileIds 和 fileId 映射）
    - 调用 IdRemapper 执行 verse.data 和 meta.data/events 的 ID 重映射
    - 更新 Verse.data 和各 Meta.data/events
    - 提交事务，返回 {verseId, metaIdMap, resourceIdMap}
    - 任何失败抛出异常触发事务回滚
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 6.1, 6.2, 6.3, 6.4, 7.1, 7.2, 7.3, 8.1, 8.2_

  - [x]* 2.3 编写 ScenePackageService 单元测试
    - 创建 `advanced/tests/unit/services/ScenePackageServiceTest.php`
    - 测试导入事务回滚场景（模拟 save 失败）
    - 测试导入 UUID 新鲜性（新 UUID 与原始不同）
    - _Requirements: 7.1, 7.2, 7.3, 8.1, 8.2, 8.3_

- [x] 3. Checkpoint - 确保核心逻辑测试通过
  - 确保所有测试通过，如有问题请告知。

- [x] 4. 创建 ScenePackageController 和路由配置
  - [x] 4.1 创建 `advanced/api/modules/v1/controllers/ScenePackageController.php`
    - 继承 `\yii\rest\Controller`
    - 配置 JWT 认证行为（与 VerseController 一致）
    - 实现 `actionExport(int $id)` 方法：
      - 查询 Verse，检查存在性（404）和权限（403 viewable）
      - 调用 ScenePackageService::buildExportData()
      - 根据 Accept 头返回 JSON 或 ZIP（Content-Type、Content-Disposition）
    - 实现 `actionImport()` 方法：
      - 根据 Content-Type 解析 JSON 请求体或 ZIP 文件上传
      - ZIP 模式：解压并从 scene.json 解析数据
      - 验证必填字段（verse.name/data/version/uuid、metas[].title/uuid、resourceFileMappings[].originalUuid/fileId/name/type/info）
      - 验证 fileId 存在性（422）
      - 调用 ScenePackageService::importScene()
      - 捕获异常返回 500
    - _Requirements: 1.1, 2.1, 2.2, 2.3, 3.1, 3.2, 3.3, 4.1, 5.1, 5.3, 5.4, 8.3, 9.1, 9.2, 9.3, 9.4, 9.5, 9.6_

  - [x] 4.2 更新路由配置 `advanced/api/config/main.php`
    - 在 urlManager.rules 中添加 ScenePackageController 的路由规则
    - 配置 `GET verses/<id:\d+>/export` 和 `POST verses/import` 的 extraPatterns
    - _Requirements: 1.1, 4.1_

  - [x]* 4.3 编写导入数据验证属性测试
    - 创建 `advanced/tests/unit/controllers/ImportValidationPropertyTest.php`
    - **Property 7: 导入数据验证拒绝不完整数据**
    - **Validates: Requirements 9.2, 9.3, 9.4**

  - [x]* 4.4 编写 Controller 单元测试
    - 创建 `advanced/tests/unit/controllers/ScenePackageControllerTest.php`
    - 测试导出权限控制（401/403/404）
    - 测试导入 ZIP 格式处理（无效 ZIP、缺少 scene.json）
    - 测试导入缺少 verse 对象返回 400
    - _Requirements: 3.1, 3.2, 3.3, 5.3, 5.4, 9.1, 9.5, 9.6_

- [x] 5. Checkpoint - 确保所有测试通过
  - 确保所有测试通过，如有问题请告知。

- [x] 6. ZIP 格式支持和集成
  - [x] 6.1 在 ScenePackageController 中完善 ZIP 导出逻辑
    - 使用 PHP ZipArchive 将 Scene_Data_Tree JSON 写入 ZIP 的 scene.json
    - 设置响应头 Content-Type: application/zip 和 Content-Disposition: attachment
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 6.2 在 ScenePackageController 中完善 ZIP 导入逻辑
    - 解析 multipart/form-data 上传的 ZIP 文件
    - 使用 ZipArchive 解压并读取 scene.json
    - 验证 ZIP 有效性和 scene.json 存在性
    - 将解析后的数据传入与 JSON 导入相同的处理流程
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [x]* 6.3 编写 ZIP 往返属性测试
    - 创建 `advanced/tests/unit/services/ScenePackagePropertyTest.php`
    - **Property 2: ZIP 序列化往返一致性**
    - **Validates: Requirements 2.2**

- [x] 7. Final checkpoint - 确保所有测试通过
  - 确保所有测试通过，如有问题请告知。

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- 属性测试使用 `eris/eris` 库，需先通过 `composer require --dev eris/eris` 安装
- IdRemapper 是纯函数，无数据库依赖，最适合属性测试
- 导入流程中 Verse 和 Meta 的 `afterSave` 行为（refreshMetas/refreshResources）需要注意：导入时 data 字段分两步写入（先空后填），需确保 afterSave 不会因空 data 而清除关联
- 所有测试文件位于 `advanced/tests/unit/` 目录下

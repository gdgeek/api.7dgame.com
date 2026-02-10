# Implementation Plan: 场景包图片导出/导入增强

## Overview

在 ScenePackageService 中新增 `createFileFromImageData()` 私有方法，修改 `importScene()` 在创建 Verse 和 Meta 时处理图片数据，并在 Controller 中新增图片数据验证。导出端无需修改。

## Tasks

- [x] 1. 新增 createFileFromImageData 方法并处理 Verse 图片导入
  - [x] 1.1 在 ScenePackageService 中新增 `createFileFromImageData(array $imageData): File` 私有方法
    - 接收图片数据数组，创建并保存 File 记录
    - 设置必填字段：url、filename、key
    - 设置可选字段：md5、type、size（存在则设置）
    - 保存失败时抛出 Exception
    - _Requirements: 3.1, 3.2, 3.3, 3.4_
  - [x] 1.2 修改 `importScene()` 的 Step 2（创建 Verse）后，处理 verse.image
    - 如果 `$verseData['image']` 存在且非 null，调用 `createFileFromImageData()` 创建 File
    - 将新 File 的 id 设置为 `$verse->image_id`，保存 Verse
    - 如果 image 不存在或为 null，跳过
    - _Requirements: 1.1, 1.2, 1.3_
  - [ ]* 1.3 编写 createFileFromImageData 的属性测试
    - **Property 2: File 字段映射正确性**
    - **Validates: Requirements 3.1, 3.2**

- [x] 2. 处理 Meta 图片导入
  - [x] 2.1 修改 `importScene()` 的 Step 4（创建 Metas 循环）内，处理每个 meta.image
    - 如果 `$metaInput['image']` 存在且非 null，调用 `createFileFromImageData()` 创建 File
    - 将新 File 的 id 设置为 `$meta->image_id`，保存 Meta
    - 如果 image 不存在或为 null，跳过
    - _Requirements: 2.1, 2.2, 2.3_
  - [ ]* 2.2 编写图片 File 创建与关联的属性测试
    - **Property 1: 图片 File 创建与关联**
    - **Validates: Requirements 1.1, 1.2, 2.1, 2.2**
  - [ ]* 2.3 编写 File ID 唯一性的属性测试
    - **Property 3: File ID 唯一性**
    - **Validates: Requirements 3.4**

- [x] 3. 新增导入数据验证
  - [x] 3.1 修改 ScenePackageController::validateImportData()，新增图片数据验证
    - 如果 `verse.image` 存在且非 null，验证 url、filename、key 必须存在
    - 如果 `metas[].image` 存在且非 null，验证 url、filename、key 必须存在
    - 缺少必填字段时抛出 BadRequestHttpException 并指明缺失字段
    - image 字段不存在或为 null 时跳过验证
    - _Requirements: 4.1, 4.2, 4.3_
  - [ ]* 3.2 编写验证逻辑的属性测试
    - **Property 4: 不完整图片数据验证**
    - **Validates: Requirements 4.1, 4.2**

- [x] 4. Checkpoint - 确保所有测试通过
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. 更新 importScene 返回值并添加单元测试
  - [x] 5.1 在 importScene() 返回值中新增 `fileIdMap` 字段
    - 记录原始 File ID 到新 File ID 的映射
    - _Requirements: 3.4_
  - [ ]* 5.2 编写单元测试覆盖边界情况
    - 测试完整图片数据的导入
    - 测试仅必填字段的图片数据导入
    - 测试 null image 和缺失 image 的处理
    - 测试 verse 和 meta 同时有图片的场景
    - _Requirements: 1.1, 1.2, 2.1, 2.2, 4.3_

- [x] 6. Final checkpoint - 确保所有测试通过
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- **JSON 和 ZIP 导入共用同一套底层逻辑（ScenePackageService::importScene），不重复实现。** Controller 只负责解析请求格式，所有 File 创建和 image_id 关联逻辑统一在 Service 层。
- Tasks marked with `*` are optional and can be skipped for faster MVP
- 导出端（buildVerseData、buildMetaData、buildFileData）无需修改，图片信息已包含在导出数据中
- ZIP 导入无需额外处理——scene.json 中已包含图片数据，parseZipUpload() 解析后传入 importScene()
- 所有 File 创建在现有事务内完成，保证原子性
- Property tests use Eris library with PHPUnit, minimum 100 iterations

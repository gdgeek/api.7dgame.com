# 设计文档：场景包图片导出/导入增强

## 概述

本设计增强 `ScenePackageService::importScene()` 方法，使其在导入场景包时能够从导出数据中的图片信息自动创建 File 数据库记录，并将新创建的 File ID 关联到 Verse 和 Meta 的 `image_id` 字段。

导出端已经通过 `buildFileData()` 输出了完整的图片文件信息（url、md5、type、filename、size、key），无需修改。核心变更集中在导入流程和验证逻辑。

不涉及实际文件的下载或上传——仅创建指向相同 COS URL 的数据库记录。

## 架构

变更范围限定在两个文件：

```
ScenePackageController.php  ← 新增图片数据验证（validateImportData 内）
         │
         ▼
ScenePackageService.php     ← 新增 File 记录创建 + image_id 关联（importScene 内）
```

**关键原则：JSON 和 ZIP 共用同一套底层逻辑，不重复实现。**

现有架构已保证这一点：
- `actionImport()` 从 JSON body 获取 `$data`
- `actionImportZip()` 从 ZIP 中解压 scene.json 获取 `$data`
- 两者最终都调用 `ScenePackageService::importScene($data)`

本次改动只修改 `importScene()` 内部逻辑，Controller 端无需区分来源。验证逻辑同样在 `validateImportData()` 中统一处理，JSON 和 ZIP 导入共用。

数据流：

```
JSON 导入: actionImport() → getBodyParams() → $data ─┐
                                                       ├→ validateImportData($data)
ZIP 导入:  actionImportZip() → parseZipUpload() → $data ┘        │
                                                                   ▼
                                                    importScene($data)
                                                           │
                                              createFileFromImageData()
                                                           │
                                                    File::save()
                                                           │
                                              verse.image_id / meta.image_id
```

## 组件与接口

### 1. ScenePackageService - 新增私有方法

```php
/**
 * 从导出的图片数据创建 File 记录
 * @param array $imageData 包含 url, filename, key, md5, type, size
 * @return File 新创建的 File 模型实例
 * @throws \Exception 如果 File 保存失败
 */
private function createFileFromImageData(array $imageData): File
```

### 2. ScenePackageService::importScene() - 修改点

在现有事务内，分两处插入 File 创建逻辑：

- **Step 2 之后**：处理 `verse.image`，创建 File 并设置 `verse.image_id`
- **Step 4 内**：处理每个 `meta.image`，创建 File 并设置 `meta.image_id`

### 3. ScenePackageController::validateImportData() - 修改点

新增对 `verse.image` 和 `metas[].image` 的可选字段验证：如果 image 存在，则 url、filename、key 必须存在。

## 数据模型

### 导出数据中的图片信息结构（已有，不变）

```json
{
  "verse": {
    "image": {
      "id": 42,
      "md5": "abc123...",
      "type": "image/png",
      "url": "https://cos.example.com/images/verse-preview.png",
      "filename": "verse-preview.png",
      "size": 102400,
      "key": "images/verse-preview.png"
    }
  },
  "metas": [
    {
      "image": {
        "id": 55,
        "md5": "def456...",
        "type": "image/jpeg",
        "url": "https://cos.example.com/images/meta-preview.jpg",
        "filename": "meta-preview.jpg",
        "size": 51200,
        "key": "images/meta-preview.jpg"
      }
    }
  ]
}
```

### File 记录创建映射

| 导出字段 | File 模型字段 | 是否必填 |
|---------|-------------|---------|
| url | url | 是 |
| filename | filename | 是 |
| key | key | 是 |
| md5 | md5 | 否 |
| type | type | 否 |
| size | size | 否 |
| — | user_id | 自动（beforeValidate） |

### importScene() 返回值变更

现有返回：
```php
['verseId' => int, 'metaIdMap' => array, 'resourceIdMap' => array]
```

新增 `fileIdMap` 字段（可选，用于调试/追踪）：
```php
['verseId' => int, 'metaIdMap' => array, 'resourceIdMap' => array, 'fileIdMap' => array]
```


## 正确性属性

*正确性属性是一种在系统所有有效执行中都应成立的特征或行为——本质上是关于系统应该做什么的形式化陈述。属性作为人类可读规范和机器可验证正确性保证之间的桥梁。*

### Property 1: 图片 File 创建与关联

*For any* 导入数据，如果 verse.image 或任意 meta.image 包含有效的图片信息（url、filename、key 均存在），则导入后对应的 Verse 或 Meta 的 `image_id` 应指向一条新创建的 File 记录；如果 image 为 null 或不存在，则 `image_id` 应为 null。

**Validates: Requirements 1.1, 1.2, 2.1, 2.2**

### Property 2: File 字段映射正确性

*For any* 从图片数据创建的 File 记录，其 url、filename、key 字段应与源数据完全一致；如果源数据中包含 md5、type、size，则 File 记录中对应字段也应一致。

**Validates: Requirements 3.1, 3.2**

### Property 3: File ID 唯一性

*For any* 导入操作创建的 File 记录，其 ID 应与导出数据中的原始 File ID 不同（即始终创建新记录，不复用旧 ID）。

**Validates: Requirements 3.4**

### Property 4: 不完整图片数据验证

*For any* 导入数据中存在的 image 对象，如果缺少 url、filename、key 中的任意一个必填字段，验证应拒绝该数据并返回错误。

**Validates: Requirements 4.1, 4.2**

## 错误处理

| 场景 | 处理方式 | HTTP 状态码 |
|------|---------|------------|
| image 数据存在但缺少必填字段 | Controller 抛出 BadRequestHttpException | 400 |
| File::save() 失败 | Service 抛出 Exception，事务回滚 | 500 |
| Verse 设置 image_id 后 save() 失败 | Service 抛出 Exception，事务回滚 | 500 |
| Meta 设置 image_id 后 save() 失败 | Service 抛出 Exception，事务回滚 | 500 |
| image 字段不存在或为 null | 正常处理，image_id 保持 null | — |

## 测试策略

### 属性测试（Property-Based Testing）

使用 **Eris** 库（PHP 属性测试框架）配合 PHPUnit。

每个属性测试运行至少 100 次迭代。每个测试用注释标注对应的设计属性编号。

标注格式：**Feature: scene-package-image-export, Property {number}: {property_text}**

属性测试重点：
- 生成随机的图片数据（包含/不包含可选字段、null/缺失 image 等变体）
- 验证 `createFileFromImageData()` 的字段映射正确性
- 验证验证逻辑对各种不完整数据的拒绝行为

### 单元测试

单元测试覆盖具体示例和边界情况：
- 完整图片数据的 File 创建
- 仅必填字段的 File 创建
- null image 的处理
- 缺失 image 字段的处理
- 事务回滚场景
- ZIP 导入中图片数据的传递

### 测试文件位置

- 属性测试：`advanced/tests/unit/services/ScenePackageImagePropertyTest.php`
- 单元测试：`advanced/tests/unit/services/ScenePackageImageTest.php`

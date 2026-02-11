---
inclusion: manual
---

# 场景包导入导出往返测试 (Scene Package Roundtrip Test)

## 用途

对 ScenePackageController 的 ZIP 导出/导入接口做往返一致性测试：
1. ZIP 导出指定 verse
2. 用导出的 ZIP 导入（创建新 verse）
3. 再次 ZIP 导出新 verse
4. 比较两次导出是否结构一致

## 前置条件

- API 服务运行在 `http://localhost:81/`
- 有可用的用户名和密码
- 目标 verse ID 存在且当前用户有权限访问

## 测试步骤

### 1. 获取 Token

```bash
TOKEN=$(curl -s -X POST http://localhost:81/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"USERNAME","password":"PASSWORD"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['token']['accessToken'])")
```

### 2. 第一次 ZIP 导出

```bash
curl -s -o /tmp/rt_export1.zip \
  -X GET "http://localhost:81/v1/scene-package/verses/{VERSE_ID}/export-zip" \
  -H "Authorization: Bearer $TOKEN"
unzip -o /tmp/rt_export1.zip -d /tmp/rt_export1_dir
```

### 3. ZIP 导入

```bash
RESULT=$(curl -s -X POST "http://localhost:81/v1/scene-package/verses/import-zip" \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/tmp/rt_export1.zip")
NEW_ID=$(echo "$RESULT" | python3 -c "import sys,json; print(json.load(sys.stdin)['verseId'])")
```

### 4. 第二次 ZIP 导出

```bash
curl -s -o /tmp/rt_export2.zip \
  -X GET "http://localhost:81/v1/scene-package/verses/$NEW_ID/export-zip" \
  -H "Authorization: Bearer $TOKEN"
unzip -o /tmp/rt_export2.zip -d /tmp/rt_export2_dir
```

### 5. 比较两次导出

```bash
python3 .kiro/skills/compare_scene_exports.py /tmp/rt_export1_dir/scene.json /tmp/rt_export2_dir/scene.json
```

## 比较工具

比较脚本位于 `.kiro/skills/compare_scene_exports.py`，功能：
- 检查 `verse.data`、`metas[].data`、`metas[].events` 的字段类型是否一致（应为 dict 而非 str）
- 检查 `verseCode` 和 `metaCode` 是否正确恢复（存在性 + 各字段长度）
- 忽略预期变化的字段：ID、UUID、时间戳、副本名称后缀
- 归一化 ID 引用字段（meta_id、resource、image_id、resource_id）后做结构比较

## 检查要点

| 字段 | 预期 |
|------|------|
| verse.data | dict（非 str） |
| metas[].data | dict（非 str） |
| metas[].events | dict（非 str） |
| verseCode | 存在且 blockly/lua/js 长度一致 |
| metaCode | 存在且 blockly/lua/js 长度一致 |
| verse.name | 原名 + "（副本 ...）" |
| meta.title | 原名 + "（副本 ...）" |
| resource.name | 原名 + "（副本 ...）" |

## 相关文件

- 控制器: `advanced/api/modules/v1/controllers/ScenePackageController.php`
- 服务层: `advanced/api/modules/v1/services/ScenePackageService.php`
- 比较工具: `.kiro/skills/compare_scene_exports.py`

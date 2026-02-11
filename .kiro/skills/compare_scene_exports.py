#!/usr/bin/env python3
"""
场景包导出往返一致性比较工具

用法:
    python3 .kiro/skills/compare_scene_exports.py <export1.json> <export2.json>

比较两次场景导出的 JSON 文件，忽略预期变化（ID/UUID/时间戳/副本后缀），
检查结构和数据是否一致。
"""
import json
import sys
import re
import difflib


IGNORE_KEYS = {
    "id", "author_id", "updater_id",
    "created_at", "updated_at",
    "uuid", "version",
}

ID_REF_KEYS = {"meta_id", "resource", "image_id", "resource_id"}

COPY_SUFFIX_RE = re.compile(r"（副本 \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}）")


def normalize(obj):
    """递归归一化，去除预期变化的字段。"""
    if isinstance(obj, dict):
        r = {}
        for k, v in obj.items():
            if k in IGNORE_KEYS:
                continue
            if k in ID_REF_KEYS and isinstance(v, (int, str)):
                r[k] = "__ID__"
                continue
            if k in ("name", "title") and isinstance(v, str):
                v = COPY_SUFFIX_RE.sub("", v)
            r[k] = normalize(v)
        return r
    elif isinstance(obj, list):
        return [normalize(i) for i in obj]
    return obj


def check_types(label, d):
    """检查关键字段类型。"""
    issues = []
    vd = d.get("verse", {}).get("data")
    if vd is not None and not isinstance(vd, dict):
        issues.append(f"verse.data 应为 dict，实际为 {type(vd).__name__}")

    for i, m in enumerate(d.get("metas", [])):
        md = m.get("data")
        if md is not None and not isinstance(md, dict):
            issues.append(f"metas[{i}].data 应为 dict，实际为 {type(md).__name__}")
        me = m.get("events")
        if me is not None and not isinstance(me, dict):
            issues.append(f"metas[{i}].events 应为 dict，实际为 {type(me).__name__}")

    return issues


def check_code(label, d):
    """检查 verseCode 和 metaCode 的存在性和内容长度。"""
    info = []
    vc = d.get("verse", {}).get("verseCode")
    if vc:
        parts = ", ".join(
            f"{k}={len(vc.get(k, '') or '')}" for k in ("blockly", "lua", "js")
        )
        info.append(f"verseCode: {parts}")
    else:
        info.append("verseCode: null")

    for i, m in enumerate(d.get("metas", [])):
        mc = m.get("metaCode")
        if mc:
            parts = ", ".join(
                f"{k}={len(mc.get(k, '') or '')}" for k in ("blockly", "lua", "js")
            )
            info.append(f"metas[{i}].metaCode: {parts}")
        else:
            info.append(f"metas[{i}].metaCode: null")

    return info


def main():
    if len(sys.argv) != 3:
        print(f"用法: {sys.argv[0]} <export1.json> <export2.json>")
        sys.exit(2)

    path1, path2 = sys.argv[1], sys.argv[2]
    d1 = json.load(open(path1))
    d2 = json.load(open(path2))

    all_ok = True

    # --- 字段类型检查 ---
    print("=" * 60)
    print("字段类型检查")
    print("=" * 60)
    for label, d in [("export1", d1), ("export2", d2)]:
        issues = check_types(label, d)
        if issues:
            all_ok = False
            for issue in issues:
                print(f"  ❌ {label}: {issue}")
        else:
            print(f"  ✅ {label}: verse.data/meta.data/meta.events 类型正确 (dict)")

    # --- Code 数据检查 ---
    print()
    print("=" * 60)
    print("Code 数据检查")
    print("=" * 60)
    info1 = check_code("export1", d1)
    info2 = check_code("export2", d2)
    for label, info in [("export1", info1), ("export2", info2)]:
        for line in info:
            print(f"  {label}: {line}")

    # 比较 code 长度是否一致
    if info1 == info2:
        print("  ✅ Code 数据长度完全一致")
    else:
        all_ok = False
        print("  ❌ Code 数据长度不一致")

    # --- 结构比较 ---
    print()
    print("=" * 60)
    print("结构比较（忽略 ID/UUID/时间戳/副本后缀）")
    print("=" * 60)
    n1 = normalize(d1)
    n2 = normalize(d2)
    j1 = json.dumps(n1, indent=2, ensure_ascii=False, sort_keys=True)
    j2 = json.dumps(n2, indent=2, ensure_ascii=False, sort_keys=True)

    if j1 == j2:
        print("  ✅ 两次导出结构和数据完全一致")
    else:
        all_ok = False
        print("  ❌ 存在差异：\n")
        diff = list(difflib.unified_diff(
            j1.splitlines(keepends=True),
            j2.splitlines(keepends=True),
            fromfile="export1 (原始)",
            tofile="export2 (导入后)",
            n=3,
        ))
        print("".join(diff[:200]))

    # --- 总结 ---
    print()
    print("=" * 60)
    if all_ok:
        print("🎉 往返测试通过")
    else:
        print("⚠️  往返测试发现问题，请检查上方详情")
    print("=" * 60)

    sys.exit(0 if all_ok else 1)


if __name__ == "__main__":
    main()

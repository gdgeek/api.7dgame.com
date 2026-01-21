#!/bin/bash

# 修复 Docker 镜像源问题
# 移除有问题的 USTC 镜像源，使用官方源或其他可用镜像源

echo "=== Docker 镜像源修复脚本 ==="
echo ""

# 检查是否有 daemon.json
DAEMON_JSON="$HOME/.docker/daemon.json"

if [ ! -f "$DAEMON_JSON" ]; then
    echo "未找到 daemon.json，将创建新配置"
    mkdir -p "$HOME/.docker"
fi

echo "当前配置："
cat "$DAEMON_JSON" 2>/dev/null || echo "无配置文件"
echo ""

# 备份原配置
if [ -f "$DAEMON_JSON" ]; then
    cp "$DAEMON_JSON" "$DAEMON_JSON.backup.$(date +%Y%m%d_%H%M%S)"
    echo "已备份原配置到: $DAEMON_JSON.backup.$(date +%Y%m%d_%H%M%S)"
fi

# 写入新配置（移除 USTC 镜像源）
cat > "$DAEMON_JSON" << 'EOF'
{
  "builder": {
    "gc": {
      "defaultKeepStorage": "20GB",
      "enabled": true
    }
  },
  "experimental": false,
  "features": {
    "buildkit": true
  },
  "registry-mirrors": [
    "https://hub-mirror.c.163.com",
    "https://mirror.aliyuncs.com"
  ]
}
EOF

echo ""
echo "新配置已写入："
cat "$DAEMON_JSON"
echo ""

echo "=== 重要提示 ==="
echo "1. 请手动重启 Docker Desktop 以应用新配置"
echo "2. 在 Docker Desktop 中: 右上角设置图标 -> Quit Docker Desktop -> 重新启动"
echo "3. 或者使用命令: killall Docker && open -a Docker"
echo ""
echo "重启后，运行以下命令重新构建："
echo "  docker-compose build --no-cache api"
echo "  docker-compose up -d"
echo ""

# 如果想直接从官方源拉取（不使用镜像），可以临时设置
echo "=== 临时方案（不使用镜像源）==="
echo "如果镜像源仍然有问题，可以临时禁用镜像源："
echo "1. 编辑 ~/.docker/daemon.json"
echo "2. 删除或注释掉 registry-mirrors 部分"
echo "3. 重启 Docker Desktop"
echo ""

#!/bin/bash

# Docker 本地开发环境停止脚本
# 使用方法: ./stop-docker.sh [选项]
# 选项:
#   -v, --volumes    同时删除数据卷（警告：会删除所有数据！）
#   -h, --help       显示帮助信息

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 显示帮助信息
show_help() {
    echo "Docker 本地开发环境停止脚本"
    echo ""
    echo "使用方法: ./stop-docker.sh [选项]"
    echo ""
    echo "选项:"
    echo "  -v, --volumes    同时删除数据卷（警告：会删除所有数据！）"
    echo "  -h, --help       显示帮助信息"
    echo ""
    echo "示例:"
    echo "  ./stop-docker.sh              # 停止服务但保留数据"
    echo "  ./stop-docker.sh -v           # 停止服务并删除所有数据"
}

# 解析命令行参数
REMOVE_VOLUMES=false

while [[ $# -gt 0 ]]; do
    case $1 in
        -v|--volumes)
            REMOVE_VOLUMES=true
            shift
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            echo -e "${RED}错误: 未知选项 $1${NC}"
            show_help
            exit 1
            ;;
    esac
done

echo "=========================================="
echo "  Docker 本地开发环境停止脚本"
echo "=========================================="
echo ""

# 检查 Docker Compose 是否安装
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}错误: Docker Compose 未安装${NC}"
    exit 1
fi

# 显示当前运行的容器
echo "当前运行的容器："
docker-compose ps

echo ""

if [ "$REMOVE_VOLUMES" = true ]; then
    echo -e "${YELLOW}警告: 即将停止服务并删除所有数据卷！${NC}"
    echo -e "${YELLOW}这将删除数据库、Redis 和所有持久化数据！${NC}"
    echo ""
    read -p "确定要继续吗？(输入 'yes' 确认): " confirm
    
    if [ "$confirm" != "yes" ]; then
        echo "操作已取消"
        exit 0
    fi
    
    echo ""
    echo "正在停止服务并删除数据卷..."
    docker-compose down -v
    echo -e "${GREEN}✓ 服务已停止，数据卷已删除${NC}"
else
    echo "正在停止服务（保留数据）..."
    docker-compose down
    echo -e "${GREEN}✓ 服务已停止，数据已保留${NC}"
fi

echo ""
echo "=========================================="
echo "停止完成！"
echo "=========================================="
echo ""

if [ "$REMOVE_VOLUMES" = false ]; then
    echo "数据已保留，下次启动时将使用现有数据"
    echo "如需删除所有数据，请运行: ./stop-docker.sh -v"
fi

echo ""

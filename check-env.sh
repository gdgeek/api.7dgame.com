#!/bin/bash

# 环境配置检查脚本
# 使用方法: ./check-env.sh

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "=========================================="
echo "  Docker 环境配置检查"
echo "=========================================="
echo ""

# 检查函数
check_command() {
    if command -v $1 &> /dev/null; then
        echo -e "${GREEN}✓${NC} $1 已安装"
        if [ "$2" = "version" ]; then
            VERSION=$($1 --version 2>&1 | head -n 1)
            echo "  版本: $VERSION"
        fi
        return 0
    else
        echo -e "${RED}✗${NC} $1 未安装"
        return 1
    fi
}

check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $1 存在"
        return 0
    else
        echo -e "${YELLOW}!${NC} $1 不存在"
        return 1
    fi
}

check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} $1 目录存在"
        return 0
    else
        echo -e "${YELLOW}!${NC} $1 目录不存在"
        return 1
    fi
}

# 检查必需的命令
echo -e "${BLUE}检查必需的工具...${NC}"
MISSING_TOOLS=0

if ! check_command "docker" "version"; then
    MISSING_TOOLS=$((MISSING_TOOLS+1))
    echo -e "  ${YELLOW}请安装 Docker: https://docs.docker.com/get-docker/${NC}"
fi

if ! check_command "docker-compose" "version"; then
    MISSING_TOOLS=$((MISSING_TOOLS+1))
    echo -e "  ${YELLOW}请安装 Docker Compose: https://docs.docker.com/compose/install/${NC}"
fi

check_command "git" "version" || true
check_command "make" || true

echo ""

# 检查 Docker 服务状态
echo -e "${BLUE}检查 Docker 服务状态...${NC}"
if docker info &> /dev/null; then
    echo -e "${GREEN}✓${NC} Docker 服务正在运行"
else
    echo -e "${RED}✗${NC} Docker 服务未运行"
    echo -e "  ${YELLOW}请启动 Docker 服务${NC}"
    MISSING_TOOLS=$((MISSING_TOOLS+1))
fi

echo ""

# 检查配置文件
echo -e "${BLUE}检查配置文件...${NC}"
CONFIG_ISSUES=0

if ! check_file ".env.docker"; then
    CONFIG_ISSUES=$((CONFIG_ISSUES+1))
    echo -e "  ${YELLOW}运行: cp .env.docker.example .env.docker${NC}"
fi

if ! check_file "docker-compose.yml"; then
    CONFIG_ISSUES=$((CONFIG_ISSUES+1))
    echo -e "  ${RED}docker-compose.yml 文件缺失！${NC}"
fi

if ! check_dir "jwt_keys" || ! check_file "jwt_keys/jwt-key.pem"; then
    CONFIG_ISSUES=$((CONFIG_ISSUES+1))
    echo -e "  ${YELLOW}运行: mkdir -p jwt_keys && openssl ecparam -genkey -name prime256v1 -noout -out jwt_keys/jwt-key.pem${NC}"
fi

echo ""

# 检查端口占用
echo -e "${BLUE}检查端口占用...${NC}"
PORT_ISSUES=0

check_port() {
    PORT=$1
    SERVICE=$2
    if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1 ; then
        echo -e "${YELLOW}!${NC} 端口 $PORT ($SERVICE) 已被占用"
        PROCESS=$(lsof -Pi :$PORT -sTCP:LISTEN | tail -n 1)
        echo "  进程: $PROCESS"
        PORT_ISSUES=$((PORT_ISSUES+1))
        return 1
    else
        echo -e "${GREEN}✓${NC} 端口 $PORT ($SERVICE) 可用"
        return 0
    fi
}

check_port 8081 "API" || true
check_port 8082 "Backend" || true
check_port 8080 "phpMyAdmin" || true
check_port 3306 "MySQL" || true
check_port 6379 "Redis" || true

echo ""

# 检查磁盘空间
echo -e "${BLUE}检查磁盘空间...${NC}"
AVAILABLE_SPACE=$(df -h . | awk 'NR==2 {print $4}')
echo "可用空间: $AVAILABLE_SPACE"

# 检查 Docker 镜像和容器
echo ""
echo -e "${BLUE}检查 Docker 资源...${NC}"
if docker info &> /dev/null; then
    IMAGES=$(docker images -q | wc -l)
    CONTAINERS=$(docker ps -a -q | wc -l)
    RUNNING=$(docker ps -q | wc -l)
    echo "Docker 镜像数量: $IMAGES"
    echo "Docker 容器数量: $CONTAINERS (运行中: $RUNNING)"
    
    # 检查是否有相关容器正在运行
    if docker-compose ps 2>/dev/null | grep -q "Up"; then
        echo -e "${GREEN}✓${NC} 检测到正在运行的服务"
        docker-compose ps
    fi
fi

echo ""
echo "=========================================="
echo "  检查结果汇总"
echo "=========================================="
echo ""

TOTAL_ISSUES=$((MISSING_TOOLS + CONFIG_ISSUES + PORT_ISSUES))

if [ $TOTAL_ISSUES -eq 0 ]; then
    echo -e "${GREEN}✓ 所有检查通过！环境配置正常${NC}"
    echo ""
    echo "你可以运行以下命令启动服务："
    echo "  ./start-docker.sh"
    echo "  或"
    echo "  make start"
else
    echo -e "${YELLOW}发现 $TOTAL_ISSUES 个问题需要解决：${NC}"
    [ $MISSING_TOOLS -gt 0 ] && echo "  • $MISSING_TOOLS 个必需工具未安装"
    [ $CONFIG_ISSUES -gt 0 ] && echo "  • $CONFIG_ISSUES 个配置文件缺失"
    [ $PORT_ISSUES -gt 0 ] && echo "  • $PORT_ISSUES 个端口被占用"
    echo ""
    echo "请根据上面的提示解决这些问题后再次运行此脚本"
fi

echo ""

# 提供快速修复建议
if [ $CONFIG_ISSUES -gt 0 ]; then
    echo -e "${BLUE}快速修复配置问题：${NC}"
    echo "  cp .env.docker.example .env.docker"
    echo "  mkdir -p jwt_keys"
    echo "  openssl ecparam -genkey -name prime256v1 -noout -out jwt_keys/jwt-key.pem"
    echo ""
fi

if [ $PORT_ISSUES -gt 0 ]; then
    echo -e "${BLUE}解决端口冲突：${NC}"
    echo "  1. 停止占用端口的服务"
    echo "  2. 或编辑 docker-compose.yml 修改端口映射"
    echo ""
fi

exit $TOTAL_ISSUES

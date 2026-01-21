#!/bin/bash

# Docker 本地开发环境启动脚本
# 使用方法: ./start-docker.sh

set -e

echo "=========================================="
echo "  Docker 本地开发环境启动脚本"
echo "=========================================="
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 检查 Docker 是否安装
if ! command -v docker &> /dev/null; then
    echo -e "${RED}错误: Docker 未安装，请先安装 Docker${NC}"
    exit 1
fi

# 检查 Docker Compose 是否安装
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}错误: Docker Compose 未安装，请先安装 Docker Compose${NC}"
    exit 1
fi

# 检查 .env.docker 文件是否存在
if [ ! -f ".env.docker" ]; then
    echo -e "${YELLOW}警告: .env.docker 文件不存在${NC}"
    echo "正在从 .env.docker.example 创建..."
    cp .env.docker.example .env.docker
    echo -e "${GREEN}✓ 已创建 .env.docker 文件${NC}"
    echo -e "${YELLOW}请编辑 .env.docker 文件，填入你的实际配置后再次运行此脚本${NC}"
    exit 0
fi

# 检查 JWT 密钥是否存在
if [ ! -d "jwt_keys" ] || [ ! -f "jwt_keys/jwt-key.pem" ]; then
    echo -e "${YELLOW}JWT 密钥不存在，正在生成...${NC}"
    mkdir -p jwt_keys
    openssl ecparam -genkey -name prime256v1 -noout -out jwt_keys/jwt-key.pem
    echo -e "${GREEN}✓ JWT 密钥已生成${NC}"
fi

# 停止现有容器
echo ""
echo "1. 停止现有容器..."
docker-compose down

# 构建镜像
echo ""
echo "2. 构建 Docker 镜像..."
docker-compose build

# 启动容器
echo ""
echo "3. 启动容器..."
docker-compose up -d

# 等待数据库启动
echo ""
echo "4. 等待数据库启动..."
echo "   这可能需要 30-60 秒..."

MAX_TRIES=60
TRIES=0
while [ $TRIES -lt $MAX_TRIES ]; do
    if docker-compose exec -T db mysqladmin ping -h localhost -u root -prootpassword &> /dev/null; then
        echo -e "${GREEN}✓ 数据库已就绪${NC}"
        break
    fi
    TRIES=$((TRIES+1))
    echo -n "."
    sleep 1
done

if [ $TRIES -eq $MAX_TRIES ]; then
    echo -e "${RED}错误: 数据库启动超时${NC}"
    echo "请运行 'docker-compose logs db' 查看详细日志"
    exit 1
fi

# 检查是否需要运行迁移
echo ""
echo "5. 检查数据库迁移..."
MIGRATION_CHECK=$(docker-compose exec -T api php yii migrate/history 1 2>&1 || echo "需要迁移")

if echo "$MIGRATION_CHECK" | grep -q "No migration has been done before"; then
    echo "   检测到新数据库，正在运行迁移..."
    docker-compose exec -T api php yii migrate --interactive=0
    echo -e "${GREEN}✓ 数据库迁移完成${NC}"
else
    echo "   数据库已是最新状态"
fi

# 初始化 RBAC（如果需要）
echo ""
echo "6. 检查 RBAC 权限系统..."
if docker-compose exec -T api php yii rbac/init &> /dev/null; then
    echo -e "${GREEN}✓ RBAC 初始化完成${NC}"
else
    echo "   RBAC 已初始化或不需要初始化"
fi

# 安装 Composer 依赖（如果需要）
if [ ! -d "advanced/vendor" ]; then
    echo ""
    echo "7. 安装 Composer 依赖..."
    docker-compose exec -T api composer install
    echo -e "${GREEN}✓ Composer 依赖安装完成${NC}"
fi

# 设置权限
echo ""
echo "8. 设置文件权限..."
docker-compose exec -T api chown -R www-data:www-data /var/www/html/advanced/runtime 2>/dev/null || true
docker-compose exec -T api chmod -R 777 /var/www/html/advanced/runtime 2>/dev/null || true
docker-compose exec -T api chown -R www-data:www-data /var/www/html/advanced/api/web/assets 2>/dev/null || true
docker-compose exec -T api chmod -R 777 /var/www/html/advanced/api/web/assets 2>/dev/null || true
echo -e "${GREEN}✓ 权限设置完成${NC}"

# 显示服务状态
echo ""
echo "=========================================="
echo -e "${GREEN}✓ 启动完成！${NC}"
echo "=========================================="
echo ""
echo "服务访问地址："
echo "  • API 服务:      http://localhost:8081"
echo "  • 后台应用:      http://localhost:8082"
echo "  • phpMyAdmin:    http://localhost:8080"
echo "  • MySQL:         localhost:3306"
echo "  • Redis:         localhost:6379"
echo ""
echo "常用命令："
echo "  • 查看日志:      docker-compose logs -f"
echo "  • 停止服务:      docker-compose down"
echo "  • 重启服务:      docker-compose restart"
echo "  • 进入容器:      docker-compose exec api bash"
echo ""
echo "更多帮助请查看: docker/README.zh-CN.md"
echo ""

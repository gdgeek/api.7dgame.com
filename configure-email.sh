#!/bin/bash

# 邮箱配置脚本
# 使用方法: ./configure-email.sh

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "=========================================="
echo "  腾讯企业邮箱配置向导"
echo "=========================================="
echo ""

# 检查 .env.docker 是否存在
if [ ! -f ".env.docker" ]; then
    echo -e "${RED}错误: .env.docker 文件不存在${NC}"
    echo "请先运行: cp .env.docker.example .env.docker"
    exit 1
fi

echo -e "${BLUE}当前 SMTP 配置：${NC}"
echo "  服务器: smtp.exmail.qq.com"
echo "  端口: 25"
echo "  加密: TLS"
echo ""

# 读取邮箱地址
echo -e "${YELLOW}请输入你的企业邮箱地址：${NC}"
read -p "邮箱地址 (例如: dirui@mrpp.com): " EMAIL_ADDRESS

if [ -z "$EMAIL_ADDRESS" ]; then
    echo -e "${RED}错误: 邮箱地址不能为空${NC}"
    exit 1
fi

# 读取邮箱密码
echo ""
echo -e "${YELLOW}请输入邮箱密码或授权码：${NC}"
read -s -p "密码: " EMAIL_PASSWORD
echo ""

if [ -z "$EMAIL_PASSWORD" ]; then
    echo -e "${RED}错误: 密码不能为空${NC}"
    exit 1
fi

# 读取测试收件人
echo ""
echo -e "${YELLOW}请输入测试邮件的收件人地址：${NC}"
read -p "收件人 (例如: test@qq.com): " TEST_EMAIL

if [ -z "$TEST_EMAIL" ]; then
    echo -e "${RED}错误: 收件人地址不能为空${NC}"
    exit 1
fi

# 更新 .env.docker 文件
echo ""
echo -e "${BLUE}正在更新配置文件...${NC}"

# 使用 sed 更新配置
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    sed -i '' "s|MAILER_USERNAME=.*|MAILER_USERNAME=$EMAIL_ADDRESS|g" .env.docker
    sed -i '' "s|MAILER_PASSWORD=.*|MAILER_PASSWORD=$EMAIL_PASSWORD|g" .env.docker
else
    # Linux
    sed -i "s|MAILER_USERNAME=.*|MAILER_USERNAME=$EMAIL_ADDRESS|g" .env.docker
    sed -i "s|MAILER_PASSWORD=.*|MAILER_PASSWORD=$EMAIL_PASSWORD|g" .env.docker
fi

echo -e "${GREEN}✓ 配置文件已更新${NC}"

# 重启 API 服务
echo ""
echo -e "${BLUE}正在重启 API 服务...${NC}"
docker-compose restart api

echo -e "${GREEN}✓ API 服务已重启${NC}"

# 等待服务启动
echo ""
echo -e "${BLUE}等待服务启动...${NC}"
sleep 5

# 发送测试邮件
echo ""
echo -e "${BLUE}正在发送测试邮件到: $TEST_EMAIL${NC}"
echo ""

docker-compose exec -T api php test-email.php "$TEST_EMAIL"

RESULT=$?

echo ""
echo "=========================================="
if [ $RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ 邮件配置成功！${NC}"
    echo ""
    echo "测试邮件已发送到: $TEST_EMAIL"
    echo "请检查收件箱（包括垃圾邮件文件夹）"
else
    echo -e "${RED}✗ 邮件发送失败${NC}"
    echo ""
    echo "可能的原因："
    echo "  1. 邮箱地址或密码错误"
    echo "  2. SMTP 服务未开通"
    echo "  3. 网络连接问题"
    echo "  4. 端口被封禁"
    echo ""
    echo "请查看上面的错误信息，或参考 EMAIL_CONFIG_GUIDE.md"
fi
echo "=========================================="
echo ""

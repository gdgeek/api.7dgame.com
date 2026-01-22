#!/bin/bash

# 更新 SMTP 授权码脚本
# 使用方法: ./update-smtp-auth-code.sh

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "=========================================="
echo "  更新腾讯企业邮箱 SMTP 授权码"
echo "=========================================="
echo ""

echo -e "${YELLOW}请先按照以下步骤获取授权码：${NC}"
echo ""
echo "1. 访问 https://exmail.qq.com 并登录"
echo "2. 点击右上角【设置】→【账户】"
echo "3. 找到【POP3/IMAP/SMTP服务设置】"
echo "4. 开启 SMTP 服务"
echo "5. 点击【生成授权码】"
echo "6. 完成验证后复制授权码（16位）"
echo ""

# 检查 .env.docker 是否存在
if [ ! -f ".env.docker" ]; then
    echo -e "${RED}错误: .env.docker 文件不存在${NC}"
    exit 1
fi

# 读取授权码
echo -e "${BLUE}请输入你刚才生成的 SMTP 授权码：${NC}"
read -s -p "授权码 (16位): " AUTH_CODE
echo ""

if [ -z "$AUTH_CODE" ]; then
    echo -e "${RED}错误: 授权码不能为空${NC}"
    exit 1
fi

# 更新 .env.docker 文件
echo ""
echo -e "${BLUE}正在更新配置文件...${NC}"

if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    sed -i '' "s|MAILER_PASSWORD=.*|MAILER_PASSWORD=$AUTH_CODE|g" .env.docker
else
    # Linux
    sed -i "s|MAILER_PASSWORD=.*|MAILER_PASSWORD=$AUTH_CODE|g" .env.docker
fi

echo -e "${GREEN}✓ 配置文件已更新${NC}"

# 更新 docker-compose.yml
echo ""
echo -e "${BLUE}正在更新 docker-compose.yml...${NC}"

if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s|MAILER_PASSWORD=.*|MAILER_PASSWORD=$AUTH_CODE|g" docker-compose.yml
else
    sed -i "s|MAILER_PASSWORD=.*|MAILER_PASSWORD=$AUTH_CODE|g" docker-compose.yml
fi

echo -e "${GREEN}✓ docker-compose.yml 已更新${NC}"

# 重启 API 服务
echo ""
echo -e "${BLUE}正在重启 API 服务...${NC}"
docker-compose restart api

echo -e "${GREEN}✓ API 服务已重启${NC}"

# 等待服务启动
echo ""
echo -e "${BLUE}等待服务启动...${NC}"
sleep 5

# 测试邮件发送
echo ""
echo -e "${BLUE}正在发送测试邮件到 nethz@163.com...${NC}"
echo ""

docker-compose exec -T api php yii test-email/send nethz@163.com

RESULT=$?

echo ""
echo "=========================================="
if [ $RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ 配置成功！${NC}"
    echo ""
    echo "测试邮件已发送，请检查收件箱"
else
    echo -e "${YELLOW}配置已更新，但邮件发送可能失败${NC}"
    echo ""
    echo "请检查："
    echo "  1. 授权码是否正确（16位）"
    echo "  2. SMTP 服务是否已开启"
    echo "  3. 网络连接是否正常"
    echo ""
    echo "查看详细指南: cat GET_SMTP_AUTH_CODE.md"
fi
echo "=========================================="
echo ""

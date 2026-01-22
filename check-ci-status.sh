#!/bin/bash

# GitHub Actions CI 状态检查脚本
# 使用方法: ./check-ci-status.sh

echo "=========================================="
echo "  GitHub Actions CI 状态检查"
echo "=========================================="
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 获取仓库信息
REPO_URL=$(git config --get remote.origin.url)
REPO_NAME=$(echo $REPO_URL | sed 's/.*github.com[:/]\(.*\)\.git/\1/')

echo -e "${BLUE}仓库:${NC} $REPO_NAME"
echo -e "${BLUE}分支:${NC} $(git branch --show-current)"
echo -e "${BLUE}最新提交:${NC} $(git log -1 --oneline)"
echo ""

# GitHub Actions URL
ACTIONS_URL="https://github.com/$REPO_NAME/actions"
echo -e "${GREEN}GitHub Actions 地址:${NC}"
echo "$ACTIONS_URL"
echo ""

# 检查是否安装了 gh CLI
if command -v gh &> /dev/null; then
    echo -e "${YELLOW}正在获取最新的工作流运行状态...${NC}"
    echo ""
    
    # 获取最新的工作流运行
    gh run list --limit 5 --json status,conclusion,name,createdAt,headBranch,displayTitle
    
    echo ""
    echo -e "${YELLOW}查看详细日志:${NC}"
    echo "gh run view --log"
    echo ""
    echo -e "${YELLOW}查看特定 job 日志:${NC}"
    echo "gh run view <run-id> --log"
else
    echo -e "${YELLOW}提示: 安装 GitHub CLI (gh) 可以直接查看 CI 状态${NC}"
    echo "安装方法: brew install gh"
    echo ""
    echo -e "${YELLOW}或者访问以下网址查看:${NC}"
    echo "$ACTIONS_URL"
fi

echo ""
echo "=========================================="
echo "  快速命令"
echo "=========================================="
echo ""
echo "# 查看最新工作流"
echo "gh run list --limit 5"
echo ""
echo "# 查看工作流详情"
echo "gh run view"
echo ""
echo "# 查看工作流日志"
echo "gh run view --log"
echo ""
echo "# 监控工作流（实时）"
echo "gh run watch"
echo ""
echo "# 在浏览器中打开"
echo "open $ACTIONS_URL"
echo ""

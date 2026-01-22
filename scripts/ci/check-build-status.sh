#!/bin/bash

# CI 构建状态检查脚本
# 用于检查 GitHub Actions 的构建状态

echo "======================================"
echo "CI 构建状态检查"
echo "======================================"
echo ""

# 获取最新的提交信息
echo "📋 最新提交信息："
git log -1 --pretty=format:"提交: %h%nAuthor: %an%n日期: %ad%n消息: %s%n" --date=format:'%Y-%m-%d %H:%M:%S'
echo ""
echo ""

# 显示当前分支
CURRENT_BRANCH=$(git branch --show-current)
echo "🌿 当前分支: $CURRENT_BRANCH"
echo ""

# 显示远程状态
echo "🔄 远程同步状态："
git fetch origin --quiet
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u})
BASE=$(git merge-base @ @{u})

if [ $LOCAL = $REMOTE ]; then
    echo "✅ 本地与远程同步"
elif [ $LOCAL = $BASE ]; then
    echo "⚠️  需要拉取远程更新"
elif [ $REMOTE = $BASE ]; then
    echo "⚠️  有本地提交未推送"
else
    echo "⚠️  分支已分叉"
fi
echo ""

# 显示 GitHub Actions 链接
echo "🔗 GitHub Actions 监控："
echo "   https://github.com/gdgeek/api.7dgame.com/actions"
echo ""

# 显示构建文档
echo "📚 相关文档："
echo "   - CI 构建状态: CI_BUILD_STATUS.md"
echo "   - CI 监控指南: docs/docker/CI_MONITORING_GUIDE.md"
echo "   - Docker 修复: docs/docker/DOCKER_COMPOSER_FIX.md"
echo ""

echo "======================================"
echo "提示："
echo "1. 访问上面的 GitHub Actions 链接查看实时构建状态"
echo "2. 构建通常需要 10-15 分钟完成"
echo "3. 查看 CI_BUILD_STATUS.md 了解详细信息"
echo "======================================"

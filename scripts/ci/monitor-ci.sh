#!/bin/bash

# 监控 CI 状态的脚本

echo "🔄 开始监控 CI 状态..."
echo "📍 仓库: gdgeek/api.7dgame.com"
echo "🌿 分支: develop"
echo ""

# 显示最新提交
echo "📝 最新提交:"
git log origin/develop --oneline -1
echo ""

echo "🌐 GitHub Actions 页面:"
echo "   https://github.com/gdgeek/api.7dgame.com/actions"
echo ""

echo "💡 提示:"
echo "   - 在浏览器中打开上面的链接查看详细日志"
echo "   - CI 通常需要 2-3 分钟完成"
echo "   - 如果测试失败，查看日志中的错误信息"
echo ""

# 尝试打开浏览器
if command -v open &> /dev/null; then
    echo "🚀 正在打开浏览器..."
    open "https://github.com/gdgeek/api.7dgame.com/actions"
elif command -v xdg-open &> /dev/null; then
    echo "🚀 正在打开浏览器..."
    xdg-open "https://github.com/gdgeek/api.7dgame.com/actions"
else
    echo "⚠️  无法自动打开浏览器，请手动访问上面的链接"
fi

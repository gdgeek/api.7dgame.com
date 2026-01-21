#!/bin/bash

# 检查 CI 状态的脚本

echo "检查 GitHub Actions 状态..."
echo "请访问: https://github.com/gdgeek/api.7dgame.com/actions"
echo ""
echo "最新提交:"
git log origin/develop --oneline -1
echo ""
echo "如果需要查看详细日志，请在浏览器中打开上面的链接"

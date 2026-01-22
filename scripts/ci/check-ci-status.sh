#!/bin/bash

# CI 状态检查脚本
# 使用 GitHub API 检查最新的 CI 运行状态

REPO="gdgeek/api.7dgame.com"
BRANCH="develop"

echo "🔍 正在检查 CI 状态..."
echo "📍 仓库: $REPO"
echo "🌿 分支: $BRANCH"
echo ""

# 获取最新的 workflow runs
echo "📊 最近的 CI 运行："
echo ""

# 使用 curl 调用 GitHub API
curl -s "https://api.github.com/repos/$REPO/actions/runs?branch=$BRANCH&per_page=5" | \
  python3 -c "
import sys, json
data = json.load(sys.stdin)
if 'workflow_runs' in data:
    for run in data['workflow_runs'][:5]:
        status = run['status']
        conclusion = run.get('conclusion', 'running')
        name = run['name']
        created = run['created_at'][:19].replace('T', ' ')
        commit = run['head_commit']['message'].split('\n')[0][:60]
        
        # 状态图标
        if conclusion == 'success':
            icon = '✅'
        elif conclusion == 'failure':
            icon = '❌'
        elif conclusion == 'cancelled':
            icon = '🚫'
        elif status == 'in_progress':
            icon = '🔄'
        elif status == 'queued':
            icon = '⏳'
        else:
            icon = '❓'
        
        print(f'{icon} {name}')
        print(f'   状态: {status} / {conclusion}')
        print(f'   提交: {commit}')
        print(f'   时间: {created}')
        print(f'   链接: {run[\"html_url\"]}')
        print()
else:
    print('❌ 无法获取 CI 状态')
"

echo ""
echo "💡 提示："
echo "   - ✅ = 成功"
echo "   - ❌ = 失败"
echo "   - 🔄 = 运行中"
echo "   - ⏳ = 排队中"
echo ""
echo "🌐 查看完整日志："
echo "   https://github.com/$REPO/actions"

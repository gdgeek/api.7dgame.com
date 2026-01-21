.PHONY: help start stop restart logs build clean migrate rbac composer test shell db-backup db-restore

# 默认目标
help:
	@echo "Docker 本地开发环境 - 可用命令："
	@echo ""
	@echo "  make start          - 启动所有服务"
	@echo "  make stop           - 停止所有服务（保留数据）"
	@echo "  make stop-all       - 停止所有服务并删除数据"
	@echo "  make restart        - 重启所有服务"
	@echo "  make logs           - 查看所有服务日志"
	@echo "  make logs-api       - 查看 API 服务日志"
	@echo "  make logs-app       - 查看后台应用日志"
	@echo "  make logs-db        - 查看数据库日志"
	@echo "  make build          - 重新构建镜像"
	@echo "  make clean          - 清理未使用的镜像和容器"
	@echo ""
	@echo "  make migrate        - 运行数据库迁移"
	@echo "  make rbac           - 初始化 RBAC 权限系统"
	@echo "  make composer       - 安装 Composer 依赖"
	@echo "  make composer-update - 更新 Composer 依赖"
	@echo ""
	@echo "  make test           - 运行所有测试"
	@echo "  make test-unit      - 运行单元测试"
	@echo "  make test-integration - 运行集成测试"
	@echo ""
	@echo "  make shell          - 进入 API 容器 Shell"
	@echo "  make shell-app      - 进入后台应用容器 Shell"
	@echo "  make shell-db       - 进入数据库容器 Shell"
	@echo ""
	@echo "  make db-backup      - 备份数据库"
	@echo "  make db-restore     - 恢复数据库（需要指定文件）"
	@echo "  make db-reset       - 重置数据库（删除并重新创建）"
	@echo ""
	@echo "  make status         - 查看服务状态"
	@echo "  make ps             - 查看运行中的容器"
	@echo ""

# 启动服务
start:
	@./start-docker.sh

# 停止服务（保留数据）
stop:
	@docker-compose down

# 停止服务并删除数据
stop-all:
	@./stop-docker.sh -v

# 重启服务
restart:
	@docker-compose restart

# 查看日志
logs:
	@docker-compose logs -f

logs-api:
	@docker-compose logs -f api

logs-app:
	@docker-compose logs -f app

logs-db:
	@docker-compose logs -f db

# 构建镜像
build:
	@docker-compose build

# 重新构建并启动
rebuild:
	@docker-compose up -d --build

# 清理
clean:
	@docker system prune -f

# 数据库迁移
migrate:
	@docker-compose exec api php yii migrate --interactive=0

# 创建新迁移
migrate-create:
	@read -p "输入迁移名称: " name; \
	docker-compose exec api php yii migrate/create $$name

# 初始化 RBAC
rbac:
	@docker-compose exec api php yii rbac/init

# Composer 操作
composer:
	@docker-compose exec api composer install

composer-update:
	@docker-compose exec api composer update

# 测试
test:
	@docker-compose exec api vendor/bin/codecept run

test-unit:
	@docker-compose exec api vendor/bin/codecept run unit

test-integration:
	@docker-compose exec api vendor/bin/codecept run integration

# 进入容器 Shell
shell:
	@docker-compose exec api bash

shell-app:
	@docker-compose exec app bash

shell-db:
	@docker-compose exec db bash

# 数据库备份
db-backup:
	@mkdir -p backups
	@docker-compose exec db mysqldump -u bujiaban -plocal_dev_password bujiaban > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "数据库已备份到 backups/ 目录"

# 数据库恢复
db-restore:
	@read -p "输入备份文件路径: " file; \
	docker-compose exec -T db mysql -u bujiaban -plocal_dev_password bujiaban < $$file
	@echo "数据库已恢复"

# 重置数据库
db-reset:
	@echo "警告: 这将删除所有数据！"
	@read -p "确定要继续吗？(输入 'yes' 确认): " confirm; \
	if [ "$$confirm" = "yes" ]; then \
		docker-compose down -v; \
		docker-compose up -d; \
		echo "等待数据库启动..."; \
		sleep 30; \
		docker-compose exec api php yii migrate --interactive=0; \
		docker-compose exec api php yii rbac/init; \
		echo "数据库已重置"; \
	else \
		echo "操作已取消"; \
	fi

# 查看服务状态
status:
	@docker-compose ps

ps:
	@docker-compose ps

# 清除缓存
cache-flush:
	@docker-compose exec api php yii cache/flush-all
	@echo "缓存已清除"

# 修复权限
fix-permissions:
	@docker-compose exec api chown -R www-data:www-data /var/www/html/advanced/runtime
	@docker-compose exec api chmod -R 777 /var/www/html/advanced/runtime
	@docker-compose exec api chown -R www-data:www-data /var/www/html/advanced/api/web/assets
	@docker-compose exec api chmod -R 777 /var/www/html/advanced/api/web/assets
	@echo "权限已修复"

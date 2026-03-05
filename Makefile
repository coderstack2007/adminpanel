# ─────────────────────────────────────────────────────────────────────────────
#  Makefile — удобные команды для разработки
#  Использование: make <команда>
# ─────────────────────────────────────────────────────────────────────────────

.PHONY: help up down restart build fresh logs shell tinker \
        migrate migrate-fresh seed db-shell test cache-clear \
        composer-install npm-build permissions

# Контейнер приложения по умолчанию
APP = docker compose exec app
x
# ── Справка ───────────────────────────────────────────────────────────────────
help: ## Показать список команд
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ── Docker ────────────────────────────────────────────────────────────────────
up: ## Поднять все контейнеры
	docker compose up -d

build: ## Пересобрать образы и поднять контейнеры
	docker compose up -d --build

down: ## Остановить контейнеры
	docker compose down

restart: ## Перезапустить все контейнеры
	docker compose restart

logs: ## Просмотр логов (app + queue)
	docker compose logs -f app queue

# ── Первичная настройка (для нового разработчика) ────────────────────────────
setup: ## ⚡ Полная первичная настройка проекта
	@echo "──────────────────────────────────────────"
	@echo "  Настройка Laravel Docker проекта"
	@echo "──────────────────────────────────────────"
	@[ -f .env ] || (cp .env.example .env && echo "✅  .env создан из .env.example")
	docker compose up -d --build
	@echo "⏳  Ждём готовности БД..."
	@sleep 5
	$(APP) php artisan key:generate
	$(APP) php artisan migrate --force
	@echo "✅  Проект готов → http://localhost"

seed: ## Запустить сидеры
	$(APP) php artisan db:seed

fresh: ## migrate:fresh + seed (УДАЛИТ все данные!)
	$(APP) php artisan migrate:fresh --seed

# ── Artisan ───────────────────────────────────────────────────────────────────
migrate: ## Запустить миграции
	$(APP) php artisan migrate

tinker: ## Laravel Tinker (REPL)
	$(APP) php artisan tinker

cache-clear: ## Очистить все кэши
	$(APP) php artisan cache:clear
	$(APP) php artisan config:clear
	$(APP) php artisan route:clear
	$(APP) php artisan view:clear

# ── Bash / DB ─────────────────────────────────────────────────────────────────
shell: ## Войти в контейнер app (bash)
	docker compose exec app bash

db-shell: ## Войти в MySQL
	docker compose exec db mysql -u$${DB_USERNAME:-laravel} -p$${DB_PASSWORD:-secret} $${DB_DATABASE:-laravel}

# ── Тесты ────────────────────────────────────────────────────────────────────
test: ## Запустить тесты
	$(APP) php artisan test

# ── Зависимости ───────────────────────────────────────────────────────────────
composer-install: ## Установить PHP-зависимости
	$(APP) composer install

npm-build: ## Собрать фронтенд
	$(APP) npm run build

# ── Права ────────────────────────────────────────────────────────────────────
permissions: ## Исправить права на storage и bootstrap/cache
	$(APP) chmod -R 775 storage bootstrap/cache
	$(APP) chown -R www-data:www-data storage bootstrap/cache
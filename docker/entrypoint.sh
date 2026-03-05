#!/bin/sh
set -e

echo "🚀  Laravel entrypoint starting..."

# ── 1. Генерация ключа (если не задан) ───────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "⚙️   APP_KEY не задан — генерирую..."
    php artisan key:generate --force
fi

# ── 2. Кэш конфига ───────────────────────────────────────────────────────────
if [ "$APP_ENV" = "production" ]; then
    echo "⚙️   Кэширование конфигурации (production)..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# ── 3. Миграции ──────────────────────────────────────────────────────────────
echo "🗄️   Запуск миграций..."
php artisan migrate --force --no-interaction

# ── 4. Сидер (опционально) ───────────────────────────────────────────────────
if [ "${AUTO_SEED:-false}" = "true" ]; then
    echo "🌱  Запуск сидеров..."
    php artisan db:seed --force --no-interaction
fi

echo "✅  Инициализация завершена. Запускаю PHP-FPM..."
exec php-fpm
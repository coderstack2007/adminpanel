# Laravel 12 — Boilerplate

Laravel 12 · PHP 8.4 · MySQL 8.4 · Redis 7 · Nginx · Spatie Permission · Breeze

---

## Стек технологий

| Компонент | Версия |
|-----------|--------|
| PHP | 8.4 (FPM + Alpine) |
| Laravel Framework | ^12.0 |
| Nginx | 1.27 |
| MySQL | 8.4 |
| Redis | 7.4 |
| Node.js | bundled in image |
| spatie/laravel-permission | ^7.2 |
| laravel/breeze | ^2.3 |

---

## Структура Docker-контейнеров

```
app        — PHP-FPM (бизнес-логика, Artisan)
nginx      — веб-сервер (проксирует запросы в app:9000)
db         — MySQL 8.4
redis      — Redis 7 (кэш, сессии, очереди)
queue      — Laravel Queue Worker
scheduler  — Laravel Scheduler (каждые 60 сек)
```

---

## Быстрый старт

### 1. Клонирование и настройка окружения

```bash
git clone <your-repo-url>
cd <your-project>

cp .env.example .env
```

Отредактируйте `.env`, убедитесь что указаны:

```env
APP_KEY=          # будет сгенерирован ниже
APP_URL=http://localhost

DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
DB_ROOT_PASSWORD=root_secret

REDIS_HOST=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 2. Сборка и запуск

```bash
docker compose up -d --build
```

### 3. Первичная инициализация

```bash
# Генерация ключа приложения
docker compose exec app php artisan key:generate

# Запуск миграций
docker compose exec app php artisan migrate

# (Опционально) Заполнение базы тестовыми данными
docker compose exec app php artisan db:seed
```

### 4. Открыть в браузере

```
http://localhost
```

---

## Команды разработки

### Artisan

```bash
# Выполнить любую artisan-команду
docker compose exec app php artisan <команда>

# Примеры:
docker compose exec app php artisan make:controller UserController
docker compose exec app php artisan make:model Post -m
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan route:list
docker compose exec app php artisan tinker
```

### Composer

```bash
docker compose exec app composer install
docker compose exec app composer require vendor/package
docker compose exec app composer dump-autoload
```

### NPM / Vite

```bash
# Сборка фронтенда (выполняется внутри контейнера app)
docker compose exec app npm run build

# Для разработки с hot-reload — запустите npm локально:
npm install
npm run dev
```

### Тесты

```bash
docker compose exec app php artisan test
# или
docker compose exec app ./vendor/bin/phpunit
```

---

## Управление контейнерами

```bash
# Запуск всех сервисов
docker compose up -d

# Остановка
docker compose down

# Остановка с удалением томов (ОСТОРОЖНО: удалит данные БД)
docker compose down -v

# Перезапуск одного сервиса
docker compose restart nginx

# Просмотр логов
docker compose logs -f app
docker compose logs -f queue
docker compose logs -f db

# Зайти в контейнер
docker compose exec app bash
docker compose exec db mysql -u laravel -p laravel
```

---

## Spatie Laravel Permission

Пакет уже подключён. После миграций таблицы ролей и разрешений будут созданы автоматически.

```bash
# Публикация конфига (если не опубликован)
docker compose exec app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Сброс кэша ролей
docker compose exec app php artisan permission:cache-reset
```

Пример использования в коде:

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Создание роли и разрешения
$role = Role::create(['name' => 'admin']);
$permission = Permission::create(['name' => 'edit articles']);
$role->givePermissionTo($permission);

// Назначение роли пользователю
$user->assignRole('admin');

// Проверка
$user->hasRole('admin');
$user->can('edit articles');
```

Защита роутов:

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

---

## Laravel Breeze

Аутентификация через Breeze подключена. Доступные маршруты после установки:

| Маршрут | Описание |
|---------|----------|
| `/register` | Регистрация |
| `/login` | Вход |
| `/logout` | Выход |
| `/dashboard` | Дашборд (только для авторизованных) |
| `/profile` | Профиль пользователя |

---

## Переменные окружения

| Переменная | По умолчанию | Описание |
|-----------|-------------|----------|
| `APP_PORT` | `80` | Внешний порт Nginx |
| `DB_PORT_FORWARD` | `3306` | Внешний порт MySQL |
| `REDIS_PORT_FORWARD` | `6379` | Внешний порт Redis |
| `DB_DATABASE` | `laravel` | Имя базы данных |
| `DB_USERNAME` | `laravel` | Пользователь БД |
| `DB_PASSWORD` | `secret` | Пароль БД |
| `DB_ROOT_PASSWORD` | `root_secret` | Root пароль MySQL |

---

## Структура файлов Docker

```
.
├── docker-compose.yml
├── Dockerfile
└── docker/
    ├── nginx/
    │   └── default.conf      # конфиг Nginx
    ├── php/
    │   └── local.ini         # настройки PHP
    └── mysql/
        └── my.cnf            # настройки MySQL
```

---

## Production-деплой

Перед деплоем в продакшн:

```bash
# Оптимизация Laravel
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan event:cache

# В Dockerfile уже используется --no-dev --optimize-autoloader для Composer
```

Также в `.env` установите:

```env
APP_ENV=production
APP_DEBUG=false
```

---

## Лицензия

MIT
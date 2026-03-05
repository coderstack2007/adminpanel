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

## ⚡ Быстрый старт (для нового разработчика)

```bash
git clone <your-repo-url>
cd <your-project>

make setup
```

Команда `make setup` автоматически:
- создаст `.env` из `.env.example`
- соберёт Docker-образы
- запустит контейнеры
- сгенерирует `APP_KEY`
- выполнит миграции

Откройте в браузере: **http://localhost**

---

## Структура контейнеров

```
app        — PHP-FPM (авто-миграции при старте)
nginx      — веб-сервер → app:9000
db         — MySQL 8.4  (данные в Docker volume)
redis      — Redis 7    (кэш, сессии, очереди)
queue      — Laravel Queue Worker
scheduler  — Laravel Scheduler (каждые 60 сек)
```

---

## Команды Make

```bash
make help          # список всех команд

make setup         # ⚡ полная первичная настройка
make up            # поднять контейнеры
make down          # остановить контейнеры
make build         # пересобрать образы
make restart       # перезапустить контейнеры
make logs          # логи app + queue

make migrate       # php artisan migrate
make fresh         # migrate:fresh --seed (УДАЛИТ данные!)
make seed          # php artisan db:seed
make tinker        # Laravel Tinker
make cache-clear   # очистить все кэши
make test          # запустить тесты

make shell         # bash внутри контейнера app
make db-shell      # MySQL CLI
```

---

## Ручная инициализация (без Make)

```bash
cp .env.example .env

docker compose up -d --build

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed   # опционально
```

---

## Автозапуск сидеров

При старте контейнера `app` миграции запускаются **автоматически**.
Сидеры можно включить через `.env`:

```env
AUTO_SEED=true
```

Или запустить вручную:

```bash
make seed
# или
docker compose exec app php artisan db:seed
```

---

## Подключение к БД из GUI (TablePlus, DBeaver)

```
Host:     127.0.0.1
Port:     3306  (или значение DB_PORT_FORWARD из .env)
Database: laravel
User:     laravel
Password: secret
```

---

## Artisan / Composer / NPM

```bash
# Artisan
docker compose exec app php artisan <команда>
docker compose exec app php artisan make:controller UserController
docker compose exec app php artisan route:list

# Composer
docker compose exec app composer require vendor/package

# Фронтенд
docker compose exec app npm run build
```

---

## Spatie Laravel Permission

Таблицы ролей создаются автоматически при миграции.

```php
$role = Role::create(['name' => 'admin']);
$permission = Permission::create(['name' => 'edit articles']);
$role->givePermissionTo($permission);
$user->assignRole('admin');
$user->hasRole('admin');      // true
$user->can('edit articles');  // true
```

```php
// Защита роутов
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

---

## Laravel Breeze — маршруты

| Маршрут | Описание |
|---------|----------|
| `/register` | Регистрация |
| `/login` | Вход |
| `/logout` | Выход |
| `/dashboard` | Дашборд (auth) |
| `/profile` | Профиль |

---

## Production-деплой

```env
APP_ENV=production
APP_DEBUG=false
AUTO_SEED=false
```

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan event:cache
```

---

## Переменные окружения

| Переменная | По умолчанию | Описание |
|-----------|-------------|----------|
| `APP_PORT` | `80` | Внешний порт Nginx |
| `DB_PORT_FORWARD` | `3306` | Порт MySQL на хосте |
| `REDIS_PORT_FORWARD` | `6379` | Порт Redis на хосте |
| `DB_DATABASE` | `laravel` | Имя базы данных |
| `DB_USERNAME` | `laravel` | Пользователь БД |
| `DB_PASSWORD` | `secret` | Пароль БД |
| `DB_ROOT_PASSWORD` | `root_secret` | Root пароль MySQL |
| `AUTO_SEED` | `false` | Запускать ли сидеры при старте |

---

## Структура Docker-файлов

```
.
├── Dockerfile
├── docker-compose.yml
├── Makefile
└── docker/
    ├── entrypoint.sh     ← авто-миграции и сидеры
    ├── nginx/
    │   └── default.conf
    ├── php/
    │   └── local.ini
    └── mysql/
        └── my.cnf
```

---

## Лицензия

MIT
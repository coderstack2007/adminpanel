FROM php:8.4-fpm-alpine

# ── Системные зависимости ─────────────────────────────────────────────────────
RUN apk add --no-cache \
    bash \
    curl \
    git \
    autoconf \
    make \
    g++ \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    freetype-dev \
    nodejs \
    npm \
    supervisor

# ── PHP-расширения ────────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# ── Redis extension ───────────────────────────────────────────────────────────
RUN pecl install redis && docker-php-ext-enable redis

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ── PHP-зависимости (слой кэшируется если composer.json не менялся) ───────────
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# ── Node-зависимости (слой кэшируется если package.json не менялся) ──────────
COPY package.json package-lock.json ./
RUN npm ci

# ── Копируем остальные файлы приложения ───────────────────────────────────────
COPY . .

# ── Финальная сборка Composer (post-install scripts) ─────────────────────────
RUN composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

# ── Сборка фронтенда ──────────────────────────────────────────────────────────
RUN npm run build

# ── Права доступа ─────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# ── Entrypoint ────────────────────────────────────────────────────────────────
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
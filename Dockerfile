# Stage 1: Build frontend assets with Vite
FROM node:22-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
RUN npm ci --ignore-scripts

COPY resources ./resources
COPY app ./app
RUN npm run build

# Stage 2: Install PHP Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Stage 3: Production runtime (PHP 8.3 FPM + Nginx + Supervisor)
FROM php:8.3-fpm-alpine AS app
WORKDIR /var/www/html

# Install system dependencies, Nginx, Supervisor, and PHP extensions
RUN apk add --no-cache \
    curl \
    nginx \
    supervisor \
    icu-libs \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    sqlite-libs \
    postgresql-libs \
    oniguruma \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    sqlite-dev \
    postgresql-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    zip \
    && apk del .build-deps

# Copy application source
COPY . .

# Copy built assets and Composer vendor from previous stages
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Run Laravel package discovery (without needing .env or DB)
RUN php artisan package:discover --ansi || true

# Copy Nginx, PHP, Supervisor, and entrypoint configurations
COPY docker/php.ini /usr/local/etc/php/conf.d/99-laravel.ini
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Normalize line endings, set permissions, and prepare runtime directories
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /run/nginx /var/log/supervisor \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -fsS http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

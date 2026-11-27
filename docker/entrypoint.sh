#!/bin/sh
set -e

cd /var/www/html

# Ensure writable Laravel directories exist
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Ensure SQLite database file exists when using sqlite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    SQLITE_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    if [ "$SQLITE_PATH" != ":memory:" ]; then
        mkdir -p "$(dirname "$SQLITE_PATH")"
        touch "$SQLITE_PATH"
        chown www-data:www-data "$SQLITE_PATH"
    fi
fi

# Create storage symlink if missing
if [ ! -L public/storage ]; then
    php artisan storage:link --no-interaction || true
fi

# Run migrations if explicitly enabled (with brief retry while DB starts)
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    n=0
    until [ "$n" -ge 5 ]; do
        if php artisan migrate --force --no-interaction; then
            break
        fi
        n=$((n + 1))
        echo "Waiting for database... retry $n/5"
        sleep 3
    done
    if [ "$n" -ge 5 ]; then
        echo "Database migration failed after 5 attempts."
        exit 1
    fi
fi

# Cache config, routes, and views when APP_KEY is configured
if [ -n "${APP_KEY:-}" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

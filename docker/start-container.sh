#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    if [ -f .env.docker ]; then
        cp .env.docker .env
    else
        cp .env.example .env
    fi
fi

mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs database

if [ "${DB_CONNECTION:-}" = "sqlite" ]; then
    touch "${DB_DATABASE:-/var/www/html/database/database.sqlite}"
fi

if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --no-interaction
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    attempts=0

    until php artisan migrate --force --no-interaction; do
        attempts=$((attempts + 1))

        if [ "$attempts" -ge 20 ]; then
            exit 1
        fi

        sleep 3
    done
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ] && [ ! -L public/storage ]; then
    php artisan storage:link --no-interaction || true
fi

exec "$@"

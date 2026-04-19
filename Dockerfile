FROM php:8.4-cli-bookworm

WORKDIR /var/www/html

ARG VITE_APP_NAME="River Level"
ARG VITE_REVERB_APP_KEY="river-level-app-key"
ARG VITE_REVERB_HOST="localhost"
ARG VITE_REVERB_PORT="8080"
ARG VITE_REVERB_SCHEME="http"

ENV VITE_APP_NAME="${VITE_APP_NAME}"
ENV VITE_REVERB_APP_KEY="${VITE_REVERB_APP_KEY}"
ENV VITE_REVERB_HOST="${VITE_REVERB_HOST}"
ENV VITE_REVERB_PORT="${VITE_REVERB_PORT}"
ENV VITE_REVERB_SCHEME="${VITE_REVERB_SCHEME}"
ENV VITE_ENABLE_WAYFINDER="true"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        curl \
        unzip \
        libzip-dev \
        libicu-dev \
        libsqlite3-dev \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-install \
        bcmath \
        intl \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        sockets \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock package.json package-lock.json ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

RUN npm ci

COPY . .
COPY docker/start-container.sh /usr/local/bin/start-container

RUN cp .env.docker .env \
    && mkdir -p database \
    && touch database/database.sqlite \
    && DB_CONNECTION=sqlite DB_DATABASE=/var/www/html/database/database.sqlite php artisan package:discover --ansi \
    && DB_CONNECTION=sqlite DB_DATABASE=/var/www/html/database/database.sqlite php artisan wayfinder:generate \
    && VITE_ENABLE_WAYFINDER=false npm run build

RUN chmod +x /usr/local/bin/start-container \
    && mkdir -p bootstrap/cache storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data /var/www/html

ENTRYPOINT ["start-container"]

EXPOSE 8000 8080

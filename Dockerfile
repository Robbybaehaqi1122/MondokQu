FROM php:8.2-fpm-alpine AS base

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
    curl \
    git \
    unzip \
    nginx \
    supervisor \
    nodejs \
    npm \
    linux-headers \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql bcmath ctype fileinfo mbstring xml zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx-app.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

COPY . .

RUN set -eux \
    && composer install --no-dev --no-progress --no-interaction --prefer-dist --optimize-autoloader \
    && npm ci \
    && npm run build \
    && chown -R $UID:$GID /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

FROM base AS dev

RUN apk add --no-cache \
    pdo_sqlite \
    sqlite \
    && docker-php-ext-install pdo_sqlite

RUN set -eux \
    && composer install --no-progress --no-interaction --prefer-dist

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

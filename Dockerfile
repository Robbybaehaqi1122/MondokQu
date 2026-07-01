# Stage 1: PHP dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-progress \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# Stage 2: Frontend assets
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm config set fetch-retries 5 \
    && npm config set fetch-retry-mintimeout 30000 \
    && npm config set fetch-retry-maxtimeout 120000 \
    && npm ci
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/
RUN npm run build

# Stage 3: Final PHP-FPM image
FROM php:8.3-fpm-alpine AS app

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
    curl \
    git \
    unzip \
    linux-headers \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql bcmath ctype fileinfo mbstring xml zip gd

RUN apk add --no-cache --virtual .build-deps \
    autoconf \
    g++ \
    make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=frontend /app/public/build/ /var/www/html/public/build/

RUN mkdir -p storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && ln -sfn ../../storage/app/public public/storage

EXPOSE 9000

CMD ["php-fpm"]

# Stage 4: Nginx (only public assets, no PHP)
FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY --from=frontend /app/public/build /var/www/html/public/build
COPY --from=app /var/www/html/public /var/www/html/public

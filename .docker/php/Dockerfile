FROM composer:2.8.4 AS builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-progress --no-interaction --optimize-autoloader

FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    mysql-client \
    zip \
    unzip \
    supervisor \
    libxml2-dev \
    icu-dev

RUN docker-php-ext-install pdo pdo_mysql intl opcache bcmath

WORKDIR /var/www/html

COPY --from=builder /app/vendor /var/www/html/vendor
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

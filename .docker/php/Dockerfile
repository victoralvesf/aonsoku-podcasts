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
    libcurl \
    curl-dev \
    pkgconfig \
    ca-certificates \
    openssl \
    icu-dev \
    dcron

RUN docker-php-ext-install curl pdo pdo_mysql intl opcache bcmath

WORKDIR /var/www/html

COPY --from=builder /app/vendor /var/www/html/vendor
COPY . /var/www/html

RUN mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/logs && \
    chown -R www-data:www-data /var/www/html/storage && \
    chmod -R 775 /var/www/html/storage

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Scheduler Crontab
RUN mkdir -p /var/spool/cron/crontabs
COPY schedule-crontab /var/spool/cron/crontabs/root
RUN chmod 0600 /var/spool/cron/crontabs/root \
    && touch /var/log/schedule.log \
    && chmod 0644 /var/log/schedule.log

EXPOSE 9000

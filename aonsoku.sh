#!/bin/bash

show_usage() {
    echo "Usage: $0 {start|queue|scheduler}"
    exit 1
}

set_timezone() {
    if [ -n "$APP_TIMEZONE" ]; then
        echo "Setting timezone to $APP_TIMEZONE..."
        setup-timezone -z "$APP_TIMEZONE"
    else
        echo "APP_TIMEZONE not set. Using default timezone (UTC)."
        setup-timezone -z UTC
    fi
}

# Check if an argument was provided
if [ -z "$1" ]; then
    show_usage
fi

case "$1" in
    start)
        set_timezone
        echo "Running migrations..."
        php artisan migrate --force
        echo "Seeding default user..."
        php artisan db:seed
        echo "Caching config..."
        php artisan config:cache
        echo "Caching routes..."
        php artisan route:cache
        echo "Starting php-fpm"
        php-fpm
        ;;

    queue)
        set_timezone
        echo "Waiting for the App to start..."
        sleep 10
        echo "Starting the queue worker..."
        supervisord -c /etc/supervisord.conf &
        tail -f /var/log/queue.log
        ;;

    scheduler)
        set_timezone
        echo "Starting the scheduler..."
        crond -f &
        tail -f /var/log/schedule.log
        ;;

    *)
        echo "Invalid command!"
        show_usage
        ;;
esac

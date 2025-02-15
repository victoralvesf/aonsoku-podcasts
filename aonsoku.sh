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
        echo "Running migrations and caching configurations..."
        php artisan migrate --force
        php artisan config:cache
        php artisan route:cache
        echo "Starting php-fpm"
        php-fpm
        echo "Service started successfully."
        ;;

    queue)
        set_timezone
        sleep 5
        echo "Starting the queue worker..."
        php artisan queue:work
        echo "Queue worker started successfully."
        ;;

    scheduler)
        set_timezone
        echo "Starting the scheduler..."
        crond -f &
        tail -f /var/log/schedule.log
        echo "Scheduler started successfully."
        ;;

    *)
        echo "Invalid command!"
        show_usage
        ;;
esac

#!/bin/bash

# Check if an argument was provided
if [ -z "$1" ]; then
    echo "Usage: $0 {start|queue|scheduler}"
    exit 1
fi

case "$1" in
    start)
        echo "Running migrations and caching configurations..."
        php artisan migrate --force
        php artisan config:cache
        php artisan route:cache
        echo "Starting php-fpm"
        php-fpm
        ;;

    queue)
        echo "Starting the queue worker..."
        php artisan queue:work
        ;;

    scheduler)
        echo "Starting the scheduler..."
        crond -f &
        tail -f /var/log/schedule.log
        ;;

    *)
        echo "Invalid command! Usage: $0 {start|queue|scheduler}"
        exit 1
        ;;
esac

#!/bin/bash

# Check if an argument was provided
if [ -z "$1" ]; then
    echo "Usage: $0 {start|queue|scheduler [interval_in_seconds]}"
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
        INTERVAL=${2:-60}
        echo "Starting the scheduler with an interval of $INTERVAL seconds..."
        while true; do
            php artisan schedule:run --verbose --no-interaction
            sleep $INTERVAL
        done
        ;;

    *)
        echo "Invalid command! Usage: $0 {start|queue|scheduler [interval_in_seconds]}"
        exit 1
        ;;
esac

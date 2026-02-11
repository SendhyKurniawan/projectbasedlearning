#!/bin/sh

# Exit on fail
set -e

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear caches
echo "Clearing caches..."
php artisan optimize:clear

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm

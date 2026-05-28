#!/bin/bash
set -e

cd /var/www/html

php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

php-fpm -D
nginx -g "daemon off;"

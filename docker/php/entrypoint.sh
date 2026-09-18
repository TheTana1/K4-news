#!/bin/sh
set -e

if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

exec "$@"

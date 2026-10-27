#!/bin/sh
set -e

cd /var/www/html

echo "==> Generating APP_KEY if not set..."
php artisan key:generate --force --no-interaction 2>/dev/null || true

echo "==> Running migrations..."
php artisan migrate --force --no-interaction

echo "==> Caching config & routes for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Setting storage permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "==> Linking storage..."
php artisan storage:link --force 2>/dev/null || true

echo "==> Starting services via Supervisor..."
mkdir -p /var/log/supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

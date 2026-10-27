#!/bin/sh
set -e

cd /var/www/html

# Ensure public/hot is never present in production
rm -f /var/www/html/public/hot

# Copy secret .env from Render's secret file location if exists
if [ -f /etc/secrets/.env ]; then
    echo "==> Copying .env from Render secret file..."
    cp /etc/secrets/.env /var/www/html/.env
elif [ ! -f /var/www/html/.env ]; then
    echo "==> Creating .env from container environment variables..."
    env | grep -E '^(APP_|DB_|SESSION_|CACHE_|QUEUE_|FILESYSTEM_|AWS_|GOOGLE_)' > /var/www/html/.env || true
fi

# Only generate APP_KEY if it's truly missing/empty (don't force-regenerate — breaks sessions!)
echo "==> Checking APP_KEY..."
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    echo "    APP_KEY not found, generating..."
    php artisan key:generate --no-interaction
else
    echo "    APP_KEY already set, skipping."
fi

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

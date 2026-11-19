#!/bin/sh
set -e

cd /var/www/html

# Generate APP_KEY otomatis kalau belum ada (sekali saja, aman dijalankan berkali-kali).
if [ -z "$(php artisan tinker --execute='echo config("app.key");' 2>/dev/null)" ]; then
    php artisan key:generate --force || true
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrate otomatis saat startup. Kalau tidak mau otomatis (misal di production
# yang migration dijalankan manual/CI), hapus baris ini.
php artisan migrate --force

php artisan storage:link || true

exec "$@"

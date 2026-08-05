#!/bin/sh
set -e

php artisan config:clear

if [ -n "$DB_HOST_DIRECT" ]; then
    DB_HOST="$DB_HOST_DIRECT" php artisan migrate --force
    DB_HOST="$DB_HOST_DIRECT" php artisan db:seed --class=RuleSeeder --force
else
    php artisan migrate --force
    php artisan db:seed --class=RuleSeeder --force
fi

php artisan config:cache

exec php artisan octane:frankenphp --host=0.0.0.0 --port="${PORT:-8080}" --workers=4 --max-requests=500

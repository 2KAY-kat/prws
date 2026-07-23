#!/bin/sh
set -e

# Ensure the SQLite file exists if that's the configured driver.
# Harmless no-op if DB_CONNECTION is mysql/pgsql instead.
mkdir -p database
touch database/database.sqlite

php artisan config:clear
php artisan migrate --force
php artisan db:seed --class=RuleSeeder --force
php artisan config:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
